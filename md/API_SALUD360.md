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
| `app/Http/Controllers/Api/Salud360/MedicoAgendaController.php` | `PUT medicos/{id}/agenda`: «tiene agenda» y «visible para pacientes» de un médico (solo administrador). Ver «Agenda por médico». |
| `app/Services/Salud360/AgendaMedicoReglas.php` | Las reglas de ese `PUT` sin base de datos (se prueban con phpunit). |
| `app/Http/Controllers/Api/Salud360/MedicoHistoriasClinicasController.php` | `PUT medicos/{id}/historias-clinicas`: historias clínicas habilitadas a un médico (solo administrador). Ver «Historias clínicas por médico». |
| `app/Services/Salud360/HistoriasClinicasReglas.php` | Las reglas de ese `PUT` sin base de datos (se prueban con phpunit). |
| `app/Http/Controllers/Api/Salud360/MedicoActivoController.php` | `PUT medicos/{id}/activo`: dar de baja o de alta a un médico (`medicos.activo`, solo administrador). Ver «Activar / desactivar un médico». |
| `app/Services/Salud360/EstadoMedicoReglas.php` | Las reglas de ese `PUT` sin base de datos (se prueban con phpunit). |
| `routes/api.php` | Grupo `salud360`. |
| `tests/Unit/Salud360AgendaServiceTest.php` | Pruebas de los métodos puros del servicio. |
| `tests/Unit/Salud360AgendaMedicoReglasTest.php` | Pruebas de las reglas de la agenda por médico. |
| `tests/Unit/Salud360HistoriasClinicasReglasTest.php` | Pruebas de las reglas de las historias clínicas por médico. |
| `tests/Unit/Salud360EstadoMedicoReglasTest.php` | Pruebas de las reglas de activar / desactivar un médico. |

Migraciones: `salud360_tokens` y `salud360_medico_hc` (idempotentes; las tablas también se crean solas, ver
`app/Services/Salud360/HistoriaClinicaService.php`) y `2026_11_05_000000_add_tiene_agenda_to_medicos_table`
(columna `medicos.tiene_agenda`, ver «Agenda por médico»). Sin cambios de versión de PHP/Laravel (PHP 7.1 / Laravel 5.8).

## CORS

La API acepta pedidos desde cualquier origen (`Access-Control-Allow-Origin: *`, middleware global
`Salud360Cors`), necesario para la versión web de Salud 360. No usa cookies: la autenticación va por
encabezado, así que abrir el origen no expone la sesión de la web de turnos.

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

- `medico`: datos del médico, `especialidad`, `consultorio` (`consultorio_id` = 0 si no tiene agenda), `modulos`
  (ids activos de `modulo_medicos`), `ventana_dias`, `cupo_primer_control` y `historias_clinicas`.
- `secretaria`: `consultorios` y `medicos` que puede gestionar (cada uno con el mismo detalle que arriba).

El médico de `medico` y cada uno de `secretaria.medicos[]` (y de `catalogos`) traen además `tiene_horarios`,
`tiene_agenda` y `visible_en_turnos`; ver «Agenda por médico».

`historias_clinicas` son los códigos de las historias clínicas de Salud 360 que el administrador habilitó al médico
(`pediatria`, `clinica`, `hepatologia`, `gineco`, `cardiologia`, `endocrinologia`, `hematologia`, `desarrollo_infantil`),
tabla `salud360_medico_hc` (se crea sola, como `salud360_tokens`). Se administran en la web en Administración →
Médico → "Historias clínicas Salud 360" (`POST admin_hc_medico`) y, desde la app, con `PUT medicos/{id}/historias-clinicas`
(ver «Historias clínicas por médico»). turnosonlinebb es la identidad única de Salud 360:
un médico puede tener agenda, historias clínicas o ambas. Cada historia clínica vive en su propio sistema
(hc_pediatria, hclinica, ...); su API acepta este mismo token, lo valida con `GET auth/perfil` y ubica al usuario
local por `users.medico_id_tobb = perfil.medico.id`.

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
| GET | `pacientes/vinculados` | `medico_id`, `[bloqueados]` (0/1), `[q]` (DNI, apellido o nombre) | Los pacientes **asociados al médico**, sin paginar, sin repetir y ordenados por apellido: la unión de los que tienen vínculo (`medico_pacientes`) y los que tienen algún turno con él (`turno_registrados`). Cada uno incluye `bloqueado` y `vinculado_en`. Devuelve `total`. Ver «Pacientes del médico». |
| GET | `pacientes/buscar` | `q` (DNI, apellido, nombre o mail) | Máximo 50 |
| GET | `pacientes/pendientes` | `medico_id` | Registrados desde la app que esperan activación (`activo = 2`) |
| GET | `pacientes/{id}` | | Incluye `medicos[] {medico_id, bloqueado}` |
| POST | `pacientes` | `medico_id`, `dni`, `nombre`, `apellido`, `[telefono]`, `[mail]`, `[fecha_nacimiento]`, `[domicilio]`, `[localidad]`, `[obra_social]`, `[numero_afiliado]`, `[obra_social_plan]`, `[afiliado_obligatorio]` | El DNI no se duplica: si ya existe se vincula al médico. Solo se actualizan sus datos si ya era un paciente de ese médico; si no, la ficha queda intacta y se devuelve tal cual. `201` si es nuevo. Ver «Pacientes del médico». |
| PUT | `pacientes/{id}` | mismos campos | |
| POST | `pacientes/{id}/activar` | `medico_id` | `activo = 1`, vincula y envía el mail de activación |
| POST | `pacientes/{id}/bloqueo` | `medico_id`, `bloqueado` (0/1) | |

#### Pacientes del médico (`pacientes/vinculados` y `POST pacientes`)

Un paciente está **asociado a un médico** si cumple alguna de estas dos:

1. tiene una fila en `medico_pacientes` con ese médico (bloqueado o no);
2. tiene algún turno con él en `turno_registrados`, **de cualquier estado** (`activo` 0, 1, 2…): un turno cancelado también cuenta como «tuvo turno».
   Es la misma convención de `GET turnos/paciente/{id}`, que tampoco filtra por `activo`.

**`GET pacientes/vinculados?medico_id[&bloqueados=0|1][&q]`** devuelve la unión de las dos, una sola vez por paciente, con el mismo formato, orden
(apellido, nombre, id) y filtros (`bloqueados`, `q`) de siempre. Para los que están solo por turnos (sin fila en `medico_pacientes`):
`bloqueado = 0` y `vinculado_en = null` (no hay fecha de vínculo). `bloqueados=0` los incluye y `bloqueados=1` no. Se hace en una sola consulta
(sin N+1) y no se filtra por `pacientes.activo` ni por el paciente «bloqueo» (DNI 99999), igual que antes. No cambió el `403` ni el `401`.

```json
{ "ok": true, "medico_id": 8, "total": 1, "pacientes": [
  { "id": 4101, "nombre": "Prueba", "apellido": "Dos", "dni": 90000002, "telefono": "555", "domicilio": "", "localidad": "", "mail": "",
    "nota": null, "fecha_nacimiento": null, "obra_social": "OS Dos", "numero_afiliado": "", "obra_social_plan": "", "afiliado_obligatorio": 0,
    "activo": 1, "created_at": "2026-11-05 19:45:51", "updated_at": "2026-11-05 19:45:51",
    "bloqueado": 0, "vinculado_en": null }
] }
```

(un paciente que el médico 8 tiene solo por un turno; si tuviera vínculo, `vinculado_en` sería la fecha de la fila de `medico_pacientes`).

**`POST pacientes`** (alta por DNI, sin duplicar):

| DNI | ¿Asociado al médico? | Qué pasa | HTTP | `creado` |
|---|---|---|---|---|
| nuevo | — | se crea con los datos enviados (`nombre` y `apellido` obligatorios) y se vincula | `201` | `true` |
| existente | sí | se actualizan los datos enviados (como siempre) | `200` | `false` |
| existente | **no** | **no se modifica ningún campo de `pacientes`** (ni siquiera los vacíos); solo se crea el vínculo con el médico (`medico_pacientes`) y con el consultorio (`paciente_secretarias`). La respuesta trae la ficha tal cual está en la base. No se validan `nombre`/`apellido` | `200` | `false` |

Después de ese alta el paciente ya está asociado, así que un nuevo `POST` del mismo médico sí actualiza sus datos. La regla «¿se pueden escribir los datos?» está en
`app/Services/Salud360/PacienteAltaReglas.php` (se prueba con phpunit sin base).

```bash
# Médico 1 da de alta un DNI nuevo (datos inventados)
curl -X POST "$BASE/api/salud360/pacientes" -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"medico_id":1,"dni":"90000001","nombre":"Prueba","apellido":"Uno","telefono":"111","obra_social":"OS Original"}'
# → 201 {"ok":true,"paciente":{"id":4102,"nombre":"Prueba","apellido":"Uno","dni":90000001,"telefono":"111",...,"obra_social":"OS Original",...},"creado":true}

# El médico 8 da de alta el mismo DNI con otros datos: no se pisa nada, se asocia y se devuelve la ficha original
curl -X POST "$BASE/api/salud360/pacientes" -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"medico_id":8,"dni":"90000001","nombre":"Otro","apellido":"Nombre","telefono":"999","obra_social":"OS Distinta"}'
# → 200 {"ok":true,"paciente":{"id":4102,"nombre":"Prueba","apellido":"Uno","dni":90000001,"telefono":"111",...,"obra_social":"OS Original",...},"creado":false}
```

Pendiente (aparte): `GET pacientes/buscar`, `GET pacientes/{id}` y `PUT pacientes/{id}` no limitan por médico (no resuelven `medico_id`):
cualquier usuario con token ve o edita cualquier ficha. Esta sección solo cubre `vinculados` y el alta.

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
| GET | `medicos/{id}/foto` | Foto del médico (`public/images/medicos`), imagen binaria, sin token. 404 `sin_foto` si no tiene |
| GET | `catalogos` | `consultorios`, `especialidades`, `medicos` (con `tiene_horarios`, `tiene_agenda` y `visible_en_turnos`), `obras_sociales`, `feriados` (desde 60 días atrás), `modulos`, `receta_estados`, `tipos_turno`, `hoy` |
| GET | `obras-sociales?medico_id` | Obras sociales del médico con `activo`, `importe`, `importe_reserva` |
| POST | `obras-sociales` | `medico_id`, `obra_social_id`, `[activo]`, `[importe]` |
| GET | `mensajes?medico_id` | Mensajes especiales del médico |
| POST | `mensajes` | `medico_id`, `titulo`, `descripcion`, `[valido_desde]`, `[valido_hasta]`, `[activo=1]`. Devuelve `id` |
| PUT | `mensajes/{id}` | Actualiza solo los campos enviados: `[titulo]`, `[descripcion]`, `[valido_desde]`, `[valido_hasta]`, `[activo]` (fecha en `null`/`""` la borra) |
| DELETE | `mensajes/{id}` | Borra el mensaje |
| POST | `feriados` | `fecha`, `descripcion` (solo admin) |
| DELETE | `feriados/{id}` | (solo admin) |

### Agenda por médico

Dos datos independientes por médico, que el administrador decide desde el panel de la app:

| Dato | Dónde se guarda | Qué significa |
|---|---|---|
| **Tiene agenda** | `medicos.tiene_agenda` (`tinyint(1)`, `DEFAULT 1`) | El médico maneja turnos. No se deduce de los horarios cargados. |
| **Visible para pacientes** | `medicos.castigo_automatico` («Mostrar Médico», `1` = se muestra) | El paciente lo ve en la web para pedir turno. Es la columna de siempre: no cambió su significado ni la web. |

Combinaciones válidas al escribir: sin agenda; con agenda interna (no visible); con agenda pública (visible).
**«Sin agenda y visible» no se puede escribir** (puede existir en la base de antes: se informa tal cual).

La migración rellena `tiene_agenda` con el mismo criterio de `tiene_horarios` (hay un `horario_medicos` con
`activo = 1`: 1 si tiene, 0 si no), así que al activarla ningún médico cambia de estado; `castigo_automatico` no se toca.
Mientras la migración no corrió, `tiene_agenda` y `visible_en_turnos` no se informan y la app sigue con `tiene_horarios`;
el `PUT` responde `422 no_disponible`.

#### Campos nuevos en `catalogos` y `auth/perfil`

En cada elemento de `catalogos.medicos`, en `perfil.medico` y en cada elemento de `perfil.secretaria.medicos[]`
(los tres salen de `Salud360Controller::formatearMedico`), sin quitar ni cambiar ningún campo anterior:

| Campo | Valor |
|---|---|
| `tiene_horarios` | `0` / `1`: hay horarios cargados. Sigue igual (respaldo de la app cuando no llega `tiene_agenda`). |
| `tiene_agenda` | `0` / `1`: columna `medicos.tiene_agenda`. |
| `visible_en_turnos` | `0` / `1`: `castigo_automatico == 1`. |

```json
{
  "id": 12, "nombre": "Ana", "apellido": "Pérez", "mail": "ana@ejemplo.com", "telefono": "1155550000",
  "especialidad_id": 3, "especialidad": "Clínica médica", "consultorio_id": 2, "activo": 1,
  "tiene_horarios": 1, "tiene_agenda": 1, "visible_en_turnos": 0,
  "historias_clinicas": ["clinica"]
}
```

#### `PUT medicos/{id}/agenda` (solo administrador)

`{id}` es el número de médico de turnos. Cuerpo (al menos uno de los dos, booleanos):

```json
{ "tiene_agenda": false, "visible_en_turnos": false }
```

Reglas, en este orden:

1. No es administrador → `403` `sin_permiso`.
2. Médico inexistente → `404` `no_encontrado` (se busca por `id`, esté activo o dado de baja).
3. Ningún campo, o valor que no es booleano (`true`/`false`, `1`/`0` o sus textos) → `422` `datos_invalidos`.
4. `tiene_agenda = false` → el médico queda también con `castigo_automatico = 0`, venga o no `visible_en_turnos`.
   Si el mismo pedido trae `visible_en_turnos = true` → `422` `agenda_requerida`.
5. `visible_en_turnos = true` en un médico sin agenda (ni la tiene ni la trae el pedido) → `422` `agenda_requerida`. No se guarda nada.
6. Solo `tiene_agenda = true` → la visibilidad no cambia. Apagar solo `visible_en_turnos` se puede siempre.
7. Todo en una transacción, con la fila del médico bloqueada. Solo se escribe lo que cambia: repetir el pedido no toca nada y da la misma respuesta.

No cancela ni modifica turnos, y no toca horarios, licencias ni `salud360_medico_hc`: apagar la agenda **no borra** lo cargado.

Respuesta `200`, siempre con el estado final de los dos y el aviso de turnos futuros:

```json
{ "ok": true, "medico_id": 12, "tiene_agenda": false, "visible_en_turnos": false, "turnos_futuros": 3 }
```

- `tiene_agenda` y `visible_en_turnos` van como booleanos JSON acá (en `catalogos` y `perfil` van como `0`/`1`).
- `turnos_futuros`: turnos del médico con fecha de hoy en adelante y vigentes, en todos sus consultorios. Vigente = no cancelado
  (`turno_registrados.activo` en 1 o 2, lo mismo que ocupa un horario en la agenda). Se informa siempre; importa cuando se apagó la agenda.
  Solo cuenta `turno_registrados` (las videollamadas no se gestionan desde Salud 360).

Errores (formato de siempre: `ok`, `mensaje`, `codigo`):

| HTTP | `codigo` | Cuándo | Ejemplo |
|---|---|---|---|
| 401 | `sin_token` / `token` | Sin token, o token inválido o vencido (middleware) | `{"ok":false,"mensaje":"No autenticado.","codigo":"sin_token"}` |
| 403 | `sin_permiso` | No es administrador | `{"ok":false,"mensaje":"Solo el administrador puede cambiar la agenda de un médico.","codigo":"sin_permiso"}` |
| 404 | `no_encontrado` | Médico inexistente | `{"ok":false,"mensaje":"Médico no encontrado.","codigo":"no_encontrado"}` |
| 422 | `datos_invalidos` | Cuerpo sin ninguno de los dos campos, o no booleano | `{"ok":false,"mensaje":"El campo tiene_agenda tiene que ser verdadero o falso.","codigo":"datos_invalidos"}` |
| 422 | `agenda_requerida` | Visible sin agenda (reglas 4 y 5) | `{"ok":false,"mensaje":"Para mostrar al médico a los pacientes primero tiene que tener agenda de turnos.","codigo":"agenda_requerida"}` |
| 422 | `no_disponible` | Falta correr la migración (columna `tiene_agenda`) | |

Ejemplos (datos inventados):

```bash
# Apagar la agenda del médico 12 (queda también no visible)
curl -s -X PUT https://turnosonlinebb.com/api/salud360/medicos/12/agenda \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'Authorization: Bearer <token admin>' \
  -d '{"tiene_agenda":false}'
# → {"ok":true,"medico_id":12,"tiene_agenda":false,"visible_en_turnos":false,"turnos_futuros":3}

# Dar agenda pública al médico 12
curl -s -X PUT .../medicos/12/agenda ... -d '{"tiene_agenda":true,"visible_en_turnos":true}'
# → {"ok":true,"medico_id":12,"tiene_agenda":true,"visible_en_turnos":true,"turnos_futuros":3}

# Mostrarlo sin agenda: no se puede
curl -s -X PUT .../medicos/41/agenda ... -d '{"visible_en_turnos":true}'
# → 422 {"ok":false,"mensaje":"Para mostrar al médico a los pacientes primero tiene que tener agenda de turnos.","codigo":"agenda_requerida"}
```

Qué no hace: los endpoints de `agenda/*`, `turnos/*`, `horarios/*` y `recetas/*` **no** se bloquean por «tiene agenda»
(la app solo oculta las pantallas) y las listas de la web para el paciente siguen mirando solo `castigo_automatico`.

### Historias clínicas por médico

#### `PUT medicos/{id}/historias-clinicas` (solo administrador)

Deja habilitadas al médico **exactamente** las historias clínicas indicadas; las demás quedan inactivas. Es lo mismo que la web hace con
`POST admin_hc_medico` (`HistoriaClinicaService::guardar`), pero con token. `{id}` es el número de médico de turnos. Cuerpo:

```json
{ "historias_clinicas": ["pediatria", "clinica"] }
```

`[]` deshabilita todas. Los códigos válidos son los de `historias_clinicas` (`pediatria`, `clinica`, `hepatologia`, `gineco`,
`cardiologia`, `endocrinologia`, `hematologia`, `desarrollo_infantil`); se comparan exactos (`Pediatria` o `pediatria ` no valen).

Reglas, en este orden:

1. No es administrador → `403` `sin_permiso`.
2. Médico inexistente → `404` `no_encontrado` (se busca por `id`, esté activo o dado de baja).
3. `historias_clinicas` ausente, que no es una lista (texto, `null`, objeto JSON...) o con algún elemento que no es texto → `422` `datos_invalidos`.
4. Algún código fuera del catálogo → `422` `datos_invalidos`, con el mensaje nombrando los códigos desconocidos. No se descarta ninguno en
   silencio ni se guarda el resto.
5. Los repetidos se juntan. Todo en una transacción, con la fila del médico bloqueada. Es idempotente: repetir el pedido da lo mismo y no toca nada.

Lo que se quita **no se borra**: la fila de `salud360_medico_hc` queda con `activo = 0` y se reactiva si se vuelve a pedir.
No toca agenda, licencias, horarios ni otras tablas.

Respuesta `200`, con la lista final real (los códigos activos tras guardar, en el orden del catálogo):

```json
{ "ok": true, "medico_id": 12, "historias_clinicas": ["pediatria", "clinica"] }
```

`GET catalogos` y `auth/perfil` informan el cambio de inmediato en `historias_clinicas` del médico.

Errores (formato de siempre: `ok`, `mensaje`, `codigo`):

| HTTP | `codigo` | Cuándo | Ejemplo |
|---|---|---|---|
| 401 | `sin_token` / `token` | Sin token, o token inválido o vencido (middleware) | `{"ok":false,"mensaje":"No autenticado.","codigo":"sin_token"}` |
| 403 | `sin_permiso` | No es administrador | `{"ok":false,"mensaje":"Solo el administrador puede cambiar las historias clínicas de un médico.","codigo":"sin_permiso"}` |
| 404 | `no_encontrado` | Médico inexistente | `{"ok":false,"mensaje":"Médico no encontrado.","codigo":"no_encontrado"}` |
| 422 | `datos_invalidos` | Falta `historias_clinicas`, no es lista o un elemento no es texto | `{"ok":false,"mensaje":"Falta historias_clinicas: la lista de códigos que quedan habilitadas ([] para ninguna). Códigos válidos: pediatria, clinica, ...","codigo":"datos_invalidos"}` |
| 422 | `datos_invalidos` | Códigos desconocidos (los nombra) | `{"ok":false,"mensaje":"Historia clínica desconocida: «oftalmologia». Códigos válidos: pediatria, clinica, ...","codigo":"datos_invalidos"}` |

Ejemplos (datos inventados):

```bash
# Dejar al médico 12 con pediatría y clínica
curl -s -X PUT https://turnosonlinebb.com/api/salud360/medicos/12/historias-clinicas \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'Authorization: Bearer <token admin>' \
  -d '{"historias_clinicas":["clinica","pediatria"]}'
# → {"ok":true,"medico_id":12,"historias_clinicas":["pediatria","clinica"]}

# Quitarle todas
curl -s -X PUT .../medicos/12/historias-clinicas ... -d '{"historias_clinicas":[]}'
# → {"ok":true,"medico_id":12,"historias_clinicas":[]}

# Código que no existe
curl -s -X PUT .../medicos/12/historias-clinicas ... -d '{"historias_clinicas":["pediatria","oftalmologia"]}'
# → 422 {"ok":false,"mensaje":"Historia clínica desconocida: «oftalmologia». Códigos válidos: pediatria, clinica, hepatologia, gineco, cardiologia, endocrinologia, hematologia, desarrollo_infantil.","codigo":"datos_invalidos"}
```

### Activar / desactivar un médico

#### `PUT medicos/{id}/activo` (solo administrador)

Da de baja (`false`) o de alta (`true`) a un médico escribiendo `medicos.activo` (`1`/`0`). `{id}` es el número de médico de turnos
(el `medico_id` de `GET usuarios`). Cuerpo:

```json
{ "activo": false }
```

**Solo los médicos tienen «activo».** `users` no tiene esa columna: para una secretaria o el administrador no existe ni se guarda
nada (`GET usuarios` informa `activo: 1` fijo para ellos). La app no debe ofrecer este botón para esos roles.

Reglas, en este orden:

1. No es administrador → `403` `sin_permiso`.
2. Médico inexistente → `404` `no_encontrado` (se busca por `id`, esté activo o dado de baja; así se puede reactivar).
3. `activo` ausente o que no es un booleano JSON de verdad → `422` `datos_invalidos`. Más estricto que la agenda: **no** valen `1`/`0`,
   `"true"`/`"false"`, `"si"`, `null`, números ni listas. (Un cuerpo no JSON, p. ej. `activo=false` como formulario, llega como texto y se rechaza.)
4. Se escribe `medicos.activo` y `updated_at` solo si el valor cambia, en una transacción con la fila bloqueada. Es idempotente:
   repetir el pedido da lo mismo y no toca la fila.

No toca agenda (`tiene_agenda`, `castigo_automatico`), historias clínicas (`salud360_medico_hc`), licencias, horarios, turnos ni tokens.
Desactivar y volver a activar deja al médico como estaba.

Respuesta `200`, con el estado final real leído de la base:

```json
{ "ok": true, "medico_id": 12, "activo": false }
```

(`activo` va como booleano JSON acá; en `GET usuarios` y `catalogos` va como `0`/`1`.)

**Efecto de `activo = 0`** (`activo = 1` en turnos significa «puede entrar y operar»):

- `GET usuarios` lo informa con `activo: 0`.
- **Desaparece de `GET catalogos`** (la lista de médicos solo trae `activo = 1`). Para saber que está dado de baja y poder reactivarlo, la app tiene que
  mirar `GET usuarios`, no `catalogos`.
- Su `auth/perfil` sigue respondiendo `200`, pero con `medico: null`. La agenda, los turnos, los horarios, los pacientes del médico, los mensajes,
  las recetas y las obras sociales responden `403 sin_permiso` (la API busca al médico propio con `activo = 1`); el administrador tampoco puede operar
  sobre él hasta reactivarlo (salvo este mismo `PUT`, `agenda` e `historias-clinicas`, que buscan por `id`).
- **No cierra sesiones.** El middleware `salud360` solo mira que el token exista y no haya vencido, y que el tipo de usuario sea 1, 2 o 3; no mira `medicos.activo`.
  Un médico dado de baja con un token vigente (dura 6 meses) puede seguir llamando a las rutas que no buscan al médico: `catalogos`, `auth/perfil`,
  `auth/password`, `auth/logout`, y `pacientes/buscar`, `pacientes/{id}` y `PUT pacientes/{id}` (no tienen control de rol). No se revocan los tokens.
  El login sí funciona (`Auth::attempt` no mira `activo`), pero el perfil sale sin ficha de médico.

Errores (formato de siempre: `ok`, `mensaje`, `codigo`):

| HTTP | `codigo` | Cuándo | Ejemplo |
|---|---|---|---|
| 401 | `sin_token` / `token` | Sin token, o token inválido o vencido (middleware) | `{"ok":false,"mensaje":"No autenticado.","codigo":"sin_token"}` |
| 403 | `sin_permiso` | No es administrador | `{"ok":false,"mensaje":"Solo el administrador puede activar o desactivar a un médico.","codigo":"sin_permiso"}` |
| 404 | `no_encontrado` | Médico inexistente | `{"ok":false,"mensaje":"Médico no encontrado.","codigo":"no_encontrado"}` |
| 422 | `datos_invalidos` | Falta `activo` | `{"ok":false,"mensaje":"Falta activo: tiene que ser verdadero o falso.","codigo":"datos_invalidos"}` |
| 422 | `datos_invalidos` | `activo` no es un booleano JSON | `{"ok":false,"mensaje":"El campo activo tiene que ser verdadero o falso (un booleano, no un número ni un texto).","codigo":"datos_invalidos"}` |

Ejemplos (datos inventados):

```bash
# Dar de baja al médico 12
curl -s -X PUT https://turnosonlinebb.com/api/salud360/medicos/12/activo \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'Authorization: Bearer <token admin>' \
  -d '{"activo":false}'
# → {"ok":true,"medico_id":12,"activo":false}

# Reactivarlo
curl -s -X PUT .../medicos/12/activo ... -d '{"activo":true}'
# → {"ok":true,"medico_id":12,"activo":true}

# Valor que no es booleano
curl -s -X PUT .../medicos/12/activo ... -d '{"activo":1}'
# → 422 {"ok":false,"mensaje":"El campo activo tiene que ser verdadero o falso (un booleano, no un número ni un texto).","codigo":"datos_invalidos"}
```

### Alta de usuarios (`POST usuarios`)

Solo administrador. Crea en un solo pedido (una transacción) el usuario con su contraseña y la ficha de médico o de secretaria.
Respuesta `201`: `{"ok":true,"usuario":{"id","nombre","apellido","email","tipo","rol","medico_id","secretaria_id","activo"}}`.

Cuerpo: `nombre`, `apellido`, `email`, `password` (mínimo 6), `rol` (`ADMIN`, `MEDICO` o `SECRETARIA`) y, solo para el rol médico:
`especialidad_id`, `consultorio_id`, `[telefono]`, `[sexo]`, `[historias_clinicas[]]`, `[mostrar_en_turnos]` y `[tiene_agenda]`.

**Agenda del médico recién creado** (solo rol `MEDICO`; para secretaria y administrador estos campos se ignoran y no se escribe nada de esto):

| Cuerpo | `medicos.tiene_agenda` | `castigo_automatico` («visible») |
|---|---|---|
| ninguno de los dos | `0` | `0` |
| `mostrar_en_turnos: true` (la app publicada, que no manda `tiene_agenda`) | `1` | `1` |
| `tiene_agenda: true`, `mostrar_en_turnos` falso u omitido | `1` | `0` (agenda interna) |
| `tiene_agenda: true`, `mostrar_en_turnos: true` | `1` | `1` (agenda pública) |
| `tiene_agenda: false`, `mostrar_en_turnos` falso u omitido | `0` | `0` |
| `tiene_agenda: false`, `mostrar_en_turnos: true` | `422 agenda_requerida`, no se crea nada | |

- Si `tiene_agenda` **no viene**, vale lo mismo que `mostrar_en_turnos`. Si **viene**, se guarda tal cual.
- `tiene_agenda` se acepta como `true`/`false`, `1`/`0` o sus textos; cualquier otro valor (incluido `null`) → `422 datos_invalidos`, sin crear nada.
- La columna se escribe siempre de forma explícita (su `DEFAULT 1` no se usa): un médico dado de alta sin pedir agenda no la tiene.
- Si la columna todavía no existe (falta la migración) el alta funciona como antes.

Errores (`ok`, `mensaje`, `codigo`): `403 sin_permiso`; `409 mail_repetido`; `422 datos` (nombre, mail, contraseña o rol), `422 especialidad`, `422 consultorio`;
y los de la agenda:

| HTTP | `codigo` | Ejemplo |
|---|---|---|
| 422 | `agenda_requerida` | `{"ok":false,"mensaje":"No se puede mostrar a los pacientes un médico sin agenda de turnos.","codigo":"agenda_requerida"}` |
| 422 | `datos_invalidos` | `{"ok":false,"mensaje":"El campo tiene_agenda tiene que ser verdadero o falso.","codigo":"datos_invalidos"}` |

```bash
# Médico que solo usa historia clínica: sin agenda y no visible (datos inventados)
curl -s -X POST https://turnosonlinebb.com/api/salud360/usuarios \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'Authorization: Bearer <token admin>' \
  -d '{"nombre":"Ana","apellido":"Prueba","email":"ana@ejemplo.test","password":"clave-1234","rol":"MEDICO","especialidad_id":1,"consultorio_id":10,"tiene_agenda":false,"mostrar_en_turnos":false,"historias_clinicas":["clinica"]}'
# → 201 {"ok":true,"usuario":{"id":73,"nombre":"Ana","apellido":"Prueba","email":"ana@ejemplo.test","tipo":2,"rol":"medico","medico_id":43,"secretaria_id":null,"activo":1}}
```

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
