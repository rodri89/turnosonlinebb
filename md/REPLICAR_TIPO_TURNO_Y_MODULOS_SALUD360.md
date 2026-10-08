# Cambios para replicar en el proyecto Salud360

Este documento resume los cambios hechos en **TurnosOnlineBB/TurnosImage** en esta sesión, para poder
replicar el mismo diseño en el otro proyecto. No son parches puntuales: son **dos patrones reutilizables**
(catálogo + habilitación por médico, y módulo on/off) aplicados a dos necesidades concretas, más algunas
correcciones de bugs que aparecieron en el camino.

## 1. Patrón: catálogo + habilitación por médico (tabla propia, no `modulo_medicos`)

Usado para "tipos de turno" (ver punto 2). Es el patrón a copiar cuando lo que hace falta **no es un simple
on/off**, sino que el médico elige entre **varias opciones** de una lista.

- **Tabla catálogo** (`tipo_turnos`): `id`, `nombre`, `activo`. Los ids numéricos no son arbitrarios: se
  mantuvieron los mismos valores que ya se usaban como "magic numbers" dispersos en el código viejo
  (`Util::getTipoTurno()`), para no romper nada existente, y se sumaron los nuevos al final (26, 27).
- **Tabla pivot** (`medico_tipo_turnos`): `id`, `medico` (FK), `tipo_turno` (FK), `activo`. Qué opciones
  del catálogo tiene habilitadas cada médico. Para el 95% de los médicos (un solo tipo) esta tabla está
  vacía y todo sigue funcionando igual que antes (el cambio es aditivo).
- **Servicio dedicado** (`app/Services/TipoTurnoService.php`): `catalogo()`, `habilitados($medicoId)`,
  `habilitadosConNombre($medicoId)`, `tieneMultiplesTipos($medicoId)` (gatilla toda la UI condicional),
  `esTipoValidoParaMedico($medicoId, $id)`, `estadoPara($medicoId)` (para pantallas admin), `nombre($id)`
  y `nombresPorId()` (para listados), `guardar($medicoId, array $ids)` (sincroniza qué queda habilitado).
- **Por qué una tabla propia y no reusar `modulo_medicos`**: `modulo_medicos` es para flags on/off de
  features fijas del sistema (ver punto 3). Un catálogo de *valores a elegir* necesita su propia tabla,
  siguiendo el mismo espíritu que ya existía en `app/Services/Salud360/HistoriaClinicaService.php`
  (catálogo de historias clínicas habilitadas por médico) — de hecho ese fue el modelo a copiar.

**Dónde se engancha:**
- Admin: `resources/views/turnos_admin/alta_medico.blade.php` (sección "Tipos de turno", checkboxes +
  `actualizarTipoTurnoMedico()`) → `MedicoController@adminTipoTurnoMedico` → `TipoTurnoService::guardar()`.
- "Mis horarios fijos" (`MedicoController@adminHorariosFijos` / `@guardarHorarioFijo`,
  `resources/views/turnos_admin_medico/admin_horarios_fijos.blade.php`): si el médico tiene más de un tipo
  habilitado, aparece un `<select>` de tipo de turno al cargar cada horario; si no, se sigue grabando
  `tipo_turno = 1` como siempre.
- Paciente: `resources/views/turnos/seleccionar_tipo_turno.blade.php` — si el médico tiene múltiples tipos,
  se renderizan botones dinámicos (uno por tipo habilitado, salvo 1 y 2 que ya tienen su botón fijo). Si
  además NO tiene el tipo 1 ("Consulta") entre los habilitados, se oculta el botón genérico "Presencial".
- `PacienteController::tipoTurno()`: antes forzaba `tipoTurno = 1` para cualquier valor que no fuera
  22/23/24 (los 3 casos especiales hardcodeados históricos). Ahora también redirige al camino correcto
  (`seleccionarDiaTurnoOnline()`, que sí respeta el tipo elegido) cuando el tipo recibido está habilitado
  para ese médico vía `TipoTurnoService::esTipoValidoParaMedico()`.
- La disponibilidad real de horarios (`AgendaService::horariosDelDia()`, `TurnoController::getHorariosMedico`,
  `diasHabilitados()`) **ya filtraba por `horario_medicos.tipo_turno`** de antes — no hizo falta tocarla.

## 2. Feature concreta: "Entrevista con padres" / "Consulta y evaluación"

Dos especialistas de un mismo consultorio necesitaban dos tipos de turno con horarios distintos dentro de
su misma agenda semanal, sin seguir hardcodeando `if($medico->id == N)` (que era el patrón dominante en
todo el código: 3 copias casi idénticas de ese dispatcher en `MedicoController`, `TurnoController` y
`PacienteController`, más otra cascada igual en los blades de selección de turno).

Se resolvió aplicando el patrón del punto 1 con ids 26="Entrevista con padres" y 27="Consulta y
evaluación". **Para replicarlo en otro proyecto con otra necesidad similar**, el único trabajo real es:
crear las 2 tablas, el servicio, enganchar los 2 puntos de UI (admin de horarios + selector del paciente),
y el ajuste puntual en el controller que decide a qué pantalla redirigir según el tipo elegido.

## 3. Patrón: módulo on/off por médico (reusa `modulo_medicos`, ya existente)

Usado para "Diferencial por obra social" (ver punto 4). Es el patrón a copiar cuando lo que hace falta es
un simple **activado/desactivado** por médico de algo que ya existe en el sistema, sin opciones múltiples.

Mecanismo (ya existía, no es nuevo): tabla `modulos` (catálogo, id + descripcion + activo) + tabla
`modulo_medicos` (medico + modulo + activo) + función `moduloActivo($medico_id, $modulo_id)` (duplicada en
varios controllers — `MedicoController`, `PacienteController`, `SecretariaController`, `TurnoController`,
`MercadopagoController`). Pasos para sumar un módulo nuevo (documentados también en
`app/Services/Salud360/AgendaService.php:23-30`, que centraliza las constantes conocidas):

1. Migración `add_modulo_<nombre>_to_modulos_table.php`: `if (DB::table('modulos')->where('id', ID)->exists()) return;` + insert. El id va después del más alto existente (antes de esta sesión, el 12 era el último libre; ahora quedó en 13 — revisar `SELECT MAX(id) FROM modulos` antes de elegir el próximo en otro proyecto, los ids pueden no coincidir entre bases).
2. Sumar la constante en `AgendaService.php` (ej. `MODULO_DIFERENCIAL_OBRA_SOCIAL = 13`).
3. En `MedicoController::adminModuloMedico()`: sumar el bloque `moduloExiste`/`agregarModulo`/`eliminarModulo` para el id nuevo (cada módulo está hardcodeado individualmente ahí, no hay loop genérico).
4. El checkbox en `alta_medico.blade.php` aparece solo (la sección "Asignar Módulos" ya itera todo `modulos` activo) — pero el JS `actualizarModuloMedico()` sí hay que tocarlo a mano: leer el checkbox nuevo y sumarlo al payload del POST a `/admin_modulo_medico`.
5. Donde haga falta, gatear con `$this->moduloActivo($medico_id, ID) == 1`.

**Importante al migrar a otro proyecto:** las migraciones solo crean el módulo en el catálogo, nunca
asignan médicos — eso siempre se hace a mano desde `/admin_medico` tildando el checkbox. Si el otro
proyecto ya tenía algún comportamiento hardcodeado por `medico->id` que ahora se reemplaza por el módulo,
hay que entrar a tildarlo manualmente para esos médicos después de migrar (si no, el comportamiento
desaparece para ellos hasta que alguien lo active).

## 4. Feature concreta: "Diferencial por obra social" configurable

Antes: al paciente del médico de Deportología (id=2, tipo_turno=24) se le mostraba un aviso con el valor
diferencial según su obra social; la condición estaba hardcodeada (`$medico->id == 2 && $tipoTurno == 24
|| $medico->id == 39`) **duplicada en PHP y en JS** dentro de `resources/views/turnos/seleccionar_dia.blade.php`.

Ahora es el módulo 13 ("Diferencial por obra social"). Se muestra solo si **(a)** el médico tiene el módulo
activo **y (b)** existe un valor cargado para la obra social del paciente en `obra_social_medicos` — esto
último ya lo resolvía `TurnoController::getImporteDiferencialParaPaciente()`, que devuelve
`encontrado=false` cuando no hay valor cargado; solo faltaba la condición (a).

Se propagó `$moduloDiferencialObraSocial = $this->moduloActivo($medico_id, 13)` en los 6 puntos donde se
arma `$diferencialPaciente` (`TurnoController.php` x2, `PacienteController.php` x3,
`MedicoMercadoPagoController.php` x1) y se reemplazó el hardcode por el gate `módulo && encontrado`.

**Bug de paso que se corrigió** (relevante si el otro proyecto copió este mismo código): el modal
`modal_obra_social_diferencial.blade.php` define su propia función JS `mostrarArancelDiferencial()` (abre/
cierra el modal de listado completo de obras sociales), y `seleccionar_dia.blade.php` **redefinía una
función con el mismo nombre** para usarla como gate booleano — en JS gana la última definición cargada, así
que una pisaba a la otra en silencio. Se renombró la del gate a `debeMostrarDiferencialObraSocial()`.

## 5. Corrección: "casos especiales" dejó de depender de `horario_medico_d_h_s`

`seleccionar_dia_casos_especiales.blade.php` es una cascada de `@if($medico->id == N)` con texto de
horarios **hardcodeado a mano** para ~13 médicos puntuales; eso no se tocó (sigue mandando igual que
siempre). El caso genérico (el `@else` final, usado por todos los médicos que no están en esa lista) arma
el texto del día/horario con `diaAtencionDesdeHasta()`, que antes consultaba una tabla separada y mantenida
a mano, `horario_medico_d_h_s` — y esa tabla se desincroniza del horario real apenas alguien cambia algo en
"Mis horarios fijos" (se confirmó en datos reales: para un médico, `horario_medico_d_h_s` decía "08:00 a
10:30" cuando el horario real en `horario_medicos` llegaba hasta las 12:00).

Se agregó `HorarioMedico::desdeHastaTexto($medicoId, $dia, $tipoTurno)` (`app/HorarioMedico.php`): calcula
el texto "HH:MM a HH:MM" (soporta varios rangos si hay un corte en el día) directamente de `horario_medicos`,
infiriendo la duración del turno del menor espacio entre horarios consecutivos de ese día. Las 4 copias de
`diaAtencionDesdeHasta()` (`MedicoController`, `PacienteController`, `SecretariaController`,
`TurnoController`) ahora delegan a este método para el caso no-videollamada, en vez de leer
`horario_medico_d_h_s`. El camino de videollamada (`horario_medico_dhs_videollamadas`, tabla distinta) no
se tocó. De paso quedó arreglado que, salvo el caso especial `tipo_turno==22`, ninguna de esas 4 funciones
filtraba por `tipo_turno` — así que con varios tipos de turno por médico podía mostrar el rango equivocado.

**Si el otro proyecto tiene la misma tabla `horario_medico_d_h_s`** (o un equivalente) manteniéndose a
mano, vale la pena aplicar el mismo reemplazo ahí — es la causa raíz de que el texto mostrado se desincronice
del horario real cada vez que alguien edita el horario fijo de un médico.

## 6. Otros bugs encontrados y corregidos en el camino (sin relación entre sí)

- `PacienteController.php` (`consultarPaciente`-adjacent query, especialidad médicos por consultorio):
  `from medicos mconsultarPaciente` — un alias de tabla roto (probablemente un find-and-replace accidental)
  que rompía la query porque el resto usaba el alias `m`. Se revirtió a `from medicos m`.
- `ConsultorioController::store()` + `admin_consultorios.blade.php`: el formulario de alta de consultorio
  tenía una sección de "Configuración de Estilos" con colores hardcodeados (azul + letra blanca) que viajaba
  siempre en el POST, así que **todo** consultorio nuevo quedaba con esa config pisando el estilo por
  defecto del sitio (esos mismos colores son el *fallback* que usa `modelo_plantilla.blade.php` cuando NO
  hay config — es decir, antes de este bug un consultorio nuevo heredaba el look normal). Se sacó esa
  sección del alta (ya existe un formulario separado "Configurar Estilos" para setear colores a propósito).
- `Util::getTipoTurno()` (switch hardcodeado de `tipo_turno` → nombre legible) no conocía ids nuevos y caía
  siempre a `"Consulta"` por default. Ahora primero busca en el catálogo `tipo_turnos` (cacheado en memoria
  por request) y usa el switch viejo solo como respaldo si la tabla no existe o el id no está cargado.
- Dos copias de una función JS `getTipoTurno()` (hardcodeada, **distinta** de la de PHP) en
  `turnos_admin_medico/listado_pacientes.blade.php` y `turnos_admin_secretaria/listado_pacientes.blade.php`
  (la columna "Tipo" de la agenda del día) tampoco conocían los ids nuevos — se sumaron ahí también.
- `MedicoController::guardarHorarioFijo()`: al cargar un horario con fecha "válido desde", el modelo
  Eloquent devuelve la fecha como objeto `Carbon` y Laravel la serializaba en el JSON de respuesta como ISO
  completo (`2026-10-06T00:00:00.000000Z`) en vez de `Y-m-d` plano, rompiendo el texto que arma el JS
  (asume siempre `YYYY-MM-DD`). Se formatea explícitamente antes de responder.

## Checklist para replicar en Salud360

1. Confirmar si ese proyecto tiene `modulo_medicos`/`moduloActivo()` — si no, hay que portar el mecanismo
   completo (tablas `modulos` + `modulo_medicos`, la función, la pantalla admin) antes de poder sumar un
   módulo nuevo tipo "on/off".
2. Si hace falta un catálogo de opciones a elegir (no un simple on/off), copiar el patrón del punto 1
   (tabla catálogo + tabla pivot + servicio dedicado), no forzarlo dentro de `modulo_medicos`.
3. Revisar si ese proyecto también tiene una tabla tipo `horario_medico_d_h_s` mantenida a mano para mostrar
   texto de horarios — si sí, aplicar el mismo reemplazo del punto 5.
4. Revisar `Util::getTipoTurno()` (o equivalente) por el mismo problema de catálogo hardcodeado con
   `default` silencioso.
5. Grep rápido de `$medico->id ==` / `$medico_id ==` en controllers y blades de ese proyecto: es el patrón
   que se viene reemplazando en todos estos casos, útil para ubicar más candidatos a migrar al mismo diseño.
