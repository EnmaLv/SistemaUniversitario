# Reporte de seguridad y auditoría — Sistema de Bienestar Estudiantil (UPTP)

Rama: `feature/seguridad-auditoria` · Fecha: 2026-10-21 · Sin push al remoto.

## 1. Resumen ejecutivo

### Implementado

| Mecanismo | Estado | Dónde |
|---|---|---|
| Bitácora de auditoría inmutable (tabla, triggers, modelo, servicio) | Completo | `bitacora`, `App\Models\Bitacora`, `App\Support\Auditoria\*` |
| Registro de peticiones HTTP autenticadas (sin polling ni recursos estáticos) | Completo | `App\Http\Middleware\RegistrarActividad` |
| Registro de cambios Eloquent (crear, actualizar, eliminar, restaurar) sin campos sensibles | Completo | `App\Support\Auditoria\AuditorModelos` |
| Registro manual de pivotes de roles, permisos, módulos y roles de usuario | Completo | `App\Support\Auditoria\CambiosPermisos` |
| Eventos de autenticación (login, logout, bloqueos, llave maestra, recuperación, sesión expirada) | Completo | Controladores `Auth\*`, `EmpleosController`, middlewares |
| Registro centralizado de cada 403 | Completo | Render callback en `bootstrap/app.php` + `App\Support\Seguridad\AccesosDenegados` |
| Pantalla de bitácora con filtros, detalle antes → después, exportación CSV y PDF | Completo | `admin/configuracion/bitacora` |
| Bloqueo por contraseña: temporal (3 fallos → 5 min) y permanente (3 temporales en 24 h) | Completo | `App\Support\Seguridad\Bloqueos`, `ProteccionLogin` |
| Mismo mensaje para usernames inexistentes + `RateLimiter` por IP + username | Completo | `ProteccionLogin` |
| Bloqueo por llave maestra (login de administrador y acceso a configuración) | Completo | `AdminMasterKeyController`, `EmpleosController::verifyMasterKey` |
| Límite de intentos en recuperación por preguntas de seguridad | Completo | `PasswordRecoveryController` |
| Cierre de sesión activa si la cuenta queda bloqueada | Completo | `App\Http\Middleware\VerificarUsuarioBloqueado` (web y API) |
| Bloqueo permanente por 10 accesos 403 en 10 minutos (ventana deslizante) | Completo | `AccesosDenegados::contar` |
| Vista 403 en Tailwind con intentos restantes | Completo | `resources/views/errors/403.blade.php` |
| Cierre de sesión por inactividad (15 min) con aviso 1 min antes y botón "Seguir conectado" | Completo | `CerrarSesionPorInactividad`, `components/aviso-inactividad` |
| Pantalla de bloqueos con desbloqueo confirmado y comando de emergencia | Completo | `admin/configuracion/seguridad/bloqueos`, `php artisan seguridad:desbloquear` |
| Cabeceras de seguridad y CSP en modo Report-Only | Completo | `App\Http\Middleware\CabecerasSeguridad` |
| Corrección de vulnerabilidades claras (ver sección 4) | 24 corregidas, 4 mitigadas, 5 verificadas sin riesgo | Varios |
| Pruebas automatizadas | 30 pruebas nuevas (184 aserciones), todas pasan. Suite completa: 34 pasan y 4 antiguas de becas quedan como incompletas | `tests/Feature/Seguridad/` |

### No implementado o pendiente

- Vulnerabilidades documentadas como **pendientes** en la sección 4 (requieren decisiones de diseño o migrar archivos ya guardados).
- La bitácora no registra cambios hechos con `DB::table()` fuera de los controladores de roles, permisos, empleados y registro. Los demás controladores que usan el query builder directo (por ejemplo, partes de citas, historias e inventario) solo quedan cubiertos por el registro de la petición HTTP (método, ruta, URL y código), no por el antes → después.
- Las 4 pruebas antiguas de `tests/Feature/BecaAsignacionServiceTest.php` y `BecaFormServiceTest.php` ya fallaban antes de este trabajo: usan la clase inexistente `App\Models\Becas\Beca` y escriben en `perfil.id_estatus` y en la tabla `sucursals`, que no existen en el esquema actual. Como pide la Fase 7, quedaron marcadas con `markTestIncomplete` y la razón; hay que reescribirlas con los modelos actuales. Además usan `RefreshDatabase`, así que cada corrida de la suite completa reconstruye la base de pruebas (unos 14 minutos en este equipo).
- No se agregaron variables al `.env` real: todos los valores tienen un valor por defecto en `config/seguridad.php`. La lista está en la sección 3 y en `.env.example`.

### Incidente durante el trabajo

Al ejecutar por primera vez `php artisan test` con el `phpunit.xml` original, **las pruebas corrieron `RefreshDatabase` sobre la base de desarrollo `bienestraestudiantil` y la vaciaron**. Causa: Docker inyecta las variables de `.env` en el entorno del contenedor (`env_file`), PHP las copia a `$_SERVER` y Laravel lee `$_SERVER` antes que los `<env>` de PHPUnit; además esos `<env>` no tenían `force="true"`. La base es de desarrollo y estaba autorizado recrearla, así que se reconstruyó con `migrate:fresh --seed`. **Los datos cargados a mano que no vienen de los seeders se perdieron.** Se corrigió (ver hallazgo H-03) para que no vuelva a pasar.

Después, la base de desarrollo se recreó dos veces más con `migrate:fresh --seed` para validar las Fases 1 y 2, como pide la tarea. Hoy contiene solo los datos de los seeders, más las 6 primeras filas de la bitácora, que vienen de una prueba de humo real con `curl` (tres contraseñas incorrectas de `denisenfermero@gmail.com`, el bloqueo temporal y el desbloqueo por consola). Por la inmutabilidad, esas filas no se pueden borrar; desaparecen con el próximo `migrate:fresh`.

## 2. Tablas y columnas nuevas

> **Actualización (adaptación al DER del profesor):** esta sección describe el estado previo a la adaptación. Hoy la tabla se llama `bitacora_sistema` y la de usuarios `user`, con la nomenclatura del DER (`tipo_evento_bis`, `use_id`, `datos_nuevos_bis`, `direccion_ip_bis`, `created_at`…). La equivalencia completa está en `docs/der/REPORTE_ADAPTACION.md` y las consultas actualizadas en `docs/seguridad/EVIDENCIAS_AUDITORIA.md`.

Se crearon una tabla nueva (`bitacora`) y diez columnas nuevas en `usuario`. No se renombró ni se eliminó ninguna columna existente. Las tablas del DER del profesor (`bitacora_sistema`, `user`) **no existen en esta base**; las equivalencias de abajo sirven para que el estudiante explique el mapeo cuando el sistema se integre al del profesor.

### 2.1 Tabla `bitacora` frente a `bitacora_sistema` del profesor

Migración: `database/migrations/2026_10_22_000001_create_bitacora_table.php`.

| `bitacora` (este sistema) | `bitacora_sistema` (DER del profesor) | Tipo | Nota |
|---|---|---|---|
| `id` | (PK) | bigint | — |
| `modulo` | `modulo_bis` | varchar(100) | — |
| `accion` | `tipo_evento_bis` | varchar(100) | Valores en la sección 2.3 |
| `tabla_afectada` | `tabla_afectada_bis` | varchar(100) | — |
| `registro_id` | `registro_id_bis` | bigint | Si la clave no es numérica se guarda en `observacion` |
| `usuario_id` | `use_id` | bigint, FK `usuario.id_usuario`, `ON DELETE SET NULL` | Nulo en eventos anónimos o de consola |
| `nombre_usuario` | `nombre_usuario_bis` | varchar(255) | Copia del username en ese momento; sobrevive al borrado del usuario |
| `datos_anteriores` | `datos_anteriores_bis` | json | Solo campos que cambiaron |
| `datos_modificados` | `datos_nuevos_bis` | json | Se mantiene el nombre del diseño del estudiante (`diagrama.dbml`) |
| `ip_address` | `direccion_ip_bis` | varchar(45) | IPv4 o IPv6 |
| `navegador` | `navegador_bis` | varchar(255) | Ej.: `Chrome 124.0` |
| `sistema_operativo` | `sistema_operativo_bis` | varchar(100) | Ej.: `Windows 10/11`, `Android 14` |
| `observacion` | `observacion_bis` | text | — |
| `fecha_hora` | (no aparece en el extracto del DER) | timestamp, `DEFAULT CURRENT_TIMESTAMP` | Viene de `diagrama.dbml` |
| `metodo_http` | — | varchar(10) | **Nueva**, ver justificación |
| `ruta` | — | varchar(191) | **Nueva** |
| `url` | — | varchar(2048) | **Nueva** |
| `codigo_respuesta` | — | smallint | **Nueva** |
| `nivel` | — | varchar(20), default `info` | **Nueva** |
| `session_id` | — | varchar(64) | **Nueva** |

Justificación de las columnas nuevas frente a `bitacora_sistema`:

- **`metodo_http`, `ruta`, `url`**: permiten reconstruir exactamente qué pantalla u operación se pidió (GET de consulta frente a PUT/DELETE de modificación) y distinguir eventos HTTP de los cambios de datos. La URL se guarda sin los parámetros de consulta sensibles (`password`, `token`, `master_key`, etc.).
- **`codigo_respuesta`**: diferencia una acción exitosa (200/302) de una rechazada (403/422/500). Sin esta columna no se puede saber si un intento llegó a ejecutarse.
- **`nivel`** (`info` / `advertencia` / `critico`): permite filtrar rápido lo importante en una auditoría (bloqueos permanentes y errores 5xx son `critico`; intentos fallidos, 403 y borrados son `advertencia`).
- **`session_id`**: correlaciona todos los eventos de una misma sesión, por ejemplo para demostrar que un cambio se hizo desde la sesión que inició el usuario X desde la IP Y. **Se guarda el hash SHA-256 del identificador**, no el identificador real, porque el identificador de sesión es una credencial: quien lo lea de la bitácora podría secuestrar la sesión.
- **`observacion`** ya existía en el DER del profesor (`observacion_bis`), pero no en `diagrama.dbml`; se agregó.

Índices: `usuario_id`, `modulo`, `accion`, `fecha_hora`, `ip_address`.

Inmutabilidad: triggers `trg_bitacora_sin_update` (BEFORE UPDATE) y `trg_bitacora_sin_delete` (BEFORE DELETE) que lanzan `SIGNAL SQLSTATE '45000'`. El `down()` elimina los triggers antes que la tabla. Única excepción: el `ON DELETE SET NULL` de la FK. MySQL no dispara triggers en acciones en cascada de llaves foráneas, así que al eliminar un usuario sus eventos conservan `nombre_usuario` y quedan con `usuario_id` nulo. Esto es intencional (lo pide el diseño) y no permite alterar el contenido del evento.

### 2.2 Columnas nuevas en `usuario` frente a `user` del profesor

Migración: `database/migrations/2026_10_22_000002_add_bloqueo_columns_to_usuario_table.php`.

| Columna nueva en `usuario` | Equivalente en `user` (DER) | Tipo | Justificación |
|---|---|---|---|
| `intentos_fallidos` | `intentos_fallidos_use` | smallint, default 0 | Equivalencia directa: contraseñas incorrectas seguidas |
| `bloqueado_hasta` | `bloqueado_hasta_use` | timestamp nulo | Equivalencia directa: fin del bloqueo temporal |
| `ultimo_login` | `ultimo_login_use` | timestamp nulo | Equivalencia directa |
| `bloqueado` | `status_id` (parcial) | boolean, default false | El DER usa un estado genérico (`status_id`). Aquí el bloqueo permanente es un indicador propio, para no mezclarlo con el estado administrativo del usuario ni depender de un catálogo de estados |
| `bloqueos_temporales` | — | smallint, default 0 | Cuenta los bloqueos temporales de la ventana actual; necesario para escalar a bloqueo permanente |
| `ventana_bloqueos_desde` | — | timestamp nulo | Inicio de la ventana de 24 h. Sin esta fecha no se puede saber si los bloqueos acumulados ocurrieron dentro de la ventana. No estaba en la lista de la tarea; ver decisión D-09 |
| `bloqueado_en` | — | timestamp nulo | Fecha del último bloqueo, para la pantalla de administración y la auditoría |
| `motivo_bloqueo` | — | varchar(100) | Distingue contraseña, llave maestra, recuperación, bloqueos repetidos y accesos 403 repetidos |
| `ultimo_login_ip` | — | varchar(45) | Evidencia de desde dónde se usó la cuenta por última vez |
| `intentos_llave_fallidos` | — | smallint, default 0 | Contador independiente para la llave maestra, que es un segundo factor distinto de la contraseña |

Índices nuevos: `bloqueado` y `bloqueado_hasta` (la pantalla de bloqueos filtra por ellos).

Las columnas de bloqueo **no** están en `Usuario::$fillable`: solo se modifican con asignación explícita desde `App\Support\Seguridad\Bloqueos`, nunca por asignación masiva.

### 2.3 Valores de `accion` usados

| Grupo | Acciones |
|---|---|
| Peticiones HTTP | `ver`, `crear`, `editar`, `eliminar`, `activar`, `exportar` (la acción resuelta por `ResolutorAccion` para la ruta) |
| Cambios de datos (Eloquent) | `crear`, `actualizar`, `eliminar`, `restaurar` (con `tabla_afectada` y `registro_id`) |
| Pivotes | `rol_permisos_asignados`, `rol_permisos_actualizados`, `rol_permisos_retirados`, `permisos_usuario_actualizados`, `roles_usuario_actualizados`, `roles_usuario_retirados`, `roles_usuario_registro` |
| Autenticación | `login_exitoso`, `login_fallido`, `login_bloqueado`, `login_limite_ip`, `login_credenciales_validas`, `logout`, `sesion_expirada` |
| Llave maestra | `llave_maestra_correcta`, `llave_maestra_incorrecta` |
| Recuperación | `recuperacion_iniciada`, `recuperacion_solicitud_invalida`, `recuperacion_preguntas_correctas`, `recuperacion_preguntas_incorrectas`, `recuperacion_reseteo_password`, `recuperacion_reseteo_llave` |
| Seguridad | `bloqueo_temporal`, `bloqueo_permanente`, `desbloqueo`, `sesion_cerrada_por_bloqueo`, `acceso_denegado`, `bitacora_exportada` |

## 3. Configuración

Archivo nuevo: `config/seguridad.php`. Todos los valores tienen un valor por defecto, así que **no es obligatorio** tocar `.env`. Para cambiar algún valor, agrega la variable al final de `.env` y ejecuta `php artisan optimize:clear` dentro del contenedor.

| Variable `.env` | Clave en `config/seguridad.php` | Por defecto | Significado |
|---|---|---|---|
| `SEGURIDAD_MAX_INTENTOS_LOGIN` | `max_intentos_login` | 3 | Contraseñas incorrectas seguidas antes del bloqueo temporal |
| `SEGURIDAD_MINUTOS_BLOQUEO_TEMPORAL` | `minutos_bloqueo_temporal` | 5 | Duración del bloqueo temporal (también del límite por IP) |
| `SEGURIDAD_BLOQUEOS_PARA_PERMANENTE` | `bloqueos_temporales_para_permanente` | 3 | Bloqueos temporales en la ventana que causan bloqueo permanente |
| `SEGURIDAD_HORAS_VENTANA_BLOQUEOS` | `horas_ventana_bloqueos` | 24 | Ventana para contar bloqueos temporales |
| `SEGURIDAD_MAX_INTENTOS_LLAVE` | `max_intentos_llave` | 3 | Llaves maestras incorrectas antes del bloqueo temporal |
| `SEGURIDAD_MAX_INTENTOS_RECUPERACION` | `max_intentos_recuperacion` | 3 | Respuestas de seguridad incorrectas antes del bloqueo temporal (el límite por IP es el doble) |
| `SEGURIDAD_MAX_CONSULTAS_RECUPERACION_IP` | `max_consultas_recuperacion_ip` | 10 | Consultas de "¿olvidaste tu contraseña?" por IP cada `minutos_bloqueo_temporal` |
| `SEGURIDAD_MINUTOS_VALIDEZ_RECUPERACION` | `minutos_validez_recuperacion` | 10 | Tiempo para cambiar la contraseña después de responder las preguntas |
| `SEGURIDAD_MAX_ACCESOS_DENEGADOS` | `max_accesos_denegados` | 10 | Respuestas 403 que causan bloqueo permanente |
| `SEGURIDAD_MINUTOS_VENTANA_ACCESOS` | `minutos_ventana_accesos` | 10 | Ventana deslizante para contar 403 |
| `SEGURIDAD_MINUTOS_INACTIVIDAD` | `minutos_inactividad` | 15 | Minutos sin actividad real antes de cerrar la sesión |
| `SEGURIDAD_SEGUNDOS_AVISO_INACTIVIDAD` | `segundos_aviso_inactividad` | 60 | Antelación del aviso "Seguir conectado" |
| `SEGURIDAD_PROXIES_CONFIABLES` | `proxies_confiables` | `*` | IPs de proxies inversos separadas por coma. `*` conserva el comportamiento actual (ver H-23) |
| `SEGURIDAD_BITACORA_HABILITADA` | `bitacora.habilitada` | true | Interruptor general de la bitácora |
| `SEGURIDAD_BITACORA_PETICIONES` | `bitacora.registrar_peticiones` | true | Registro de cada petición HTTP autenticada |
| `SEGURIDAD_BITACORA_MODELOS` | `bitacora.registrar_modelos` | true | Registro de cambios Eloquent |
| `SEGURIDAD_CABECERAS` | `cabeceras.habilitadas` | true | Cabeceras de seguridad |
| `SEGURIDAD_CSP_REPORT_ONLY` | `cabeceras.csp_report_only` | Política para los CDN actuales | Contenido de `Content-Security-Policy-Report-Only` |

En el mismo archivo, sin variable de entorno, están las listas de exclusión de ruido (`ruido.rutas`, `ruido.paths`, `ruido.metodos`), los campos sensibles que nunca se guardan (`campos_sensibles`, `patrones_sensibles`), los campos que no generan evento de "actualizar" (`campos_ignorados_modelos`) y los modelos excluidos.

### Valores recomendados para la sesión (`config/session.php`)

| Variable | Actual en `.env` | Recomendado | Motivo |
|---|---|---|---|
| `SESSION_LIFETIME` | 120 | **20** | Debe ser algo **mayor** que `SEGURIDAD_MINUTOS_INACTIVIDAD` (15). Si es menor, Laravel descarta la sesión antes y el cierre no queda en la bitácora como `sesion_expirada`. Si es mucho mayor, las sesiones abandonadas quedan vivas en la tabla `sessions` |
| `SESSION_HTTP_ONLY` | (no definida → true) | `true` | JavaScript no puede leer la cookie de sesión |
| `SESSION_SAME_SITE` | (no definida → `lax`) | `lax` | Protege contra CSRF sin romper enlaces entrantes. `strict` también funciona, pero obliga a iniciar sesión de nuevo al llegar desde un enlace externo |
| `SESSION_SECURE_COOKIE` | (no definida) | `true` **en producción con HTTPS**; `false` en el entorno local por HTTP | La cookie solo viaja por HTTPS |
| `SESSION_EXPIRE_ON_CLOSE` | (no definida → false) | `false`, o `true` en equipos compartidos (laboratorios) | Con `true` la sesión muere al cerrar el navegador |
| `SESSION_ENCRYPT` | false | `true` en producción | Cifra el contenido de la sesión en la tabla `sessions` |

Verificado en el código: el login regenera el ID de sesión (`$request->session()->regenerate()` en `LoginController` y `AdminMasterKeyController`) y el logout invalida la sesión y regenera el token CSRF (trait `AuthenticatesUsers::logout`, y también los cierres forzados por inactividad, bloqueo y 403 repetidos).

Estas variables y las de seguridad se agregaron a `.env.example` como referencia. El `.env` real no se modificó.

## 4. Hallazgos de vulnerabilidades

Severidad: Crítica / Alta / Media / Baja. Estado: **Corregido**, **Mitigado** (riesgo reducido, queda algo por hacer) o **Pendiente**.

| ID | Severidad | Estado | Archivo | Hallazgo | Recomendación / corrección aplicada |
|---|---|---|---|---|---|
| H-01 | Crítica | Corregido | `app/Http/Controllers/Auth/PasswordRecoveryController.php` | `resetPassword` y `resetMasterKey` solo exigían `session('recovery_user_id')`, que se fija en `postEmail` **antes** de responder las preguntas. Bastaba con enviar el username de un administrador y luego `POST password/recover/reset-password` para tomar la cuenta sin conocer las respuestas | Se exige `recovery_verified_id` (solo se fija con respuestas correctas) con validez de 10 min. `resetMasterKey` solo se permite a administradores. Límite de intentos por usuario e IP con bloqueo temporal. Prueba: `test_no_se_puede_restablecer_la_contrasena_sin_responder_las_preguntas` |
| H-02 | Crítica | Corregido | `routes/web.php` (`admin/configuracion/archivos/ver/{archivo}`) | Path traversal: `storage_path('app/public/' . $archivo)` con `where('archivo', '.*')` permitía leer `../../.env` (APP_KEY, credenciales de BD) | `realpath` + verificación de que la ruta resuelta quede dentro de `storage/app/public`; si no, 404. La ruta ahora tiene nombre (`admin.configuracion.archivos.ver`) y queda bajo el permiso `archivos`. Prueba: `test_path_traversal_en_archivos_devuelve_404` |
| H-03 | Alta | Corregido | `phpunit.xml`, `tests/TestCase.php` | `php artisan test` ejecutaba `RefreshDatabase` sobre la base de desarrollo (ver "Incidente" en la sección 1) | Base `bienestraestudiantil_test`; variables forzadas con `force="true"` en `<env>` y `<server>`; `TestCase` aborta si la base no termina en `_test` |
| H-04 | Alta | Corregido | `app/Http/Controllers/salud/HistoriaController.php` (`show`, `update`, `storeSeccion`, `vincularEnfermedad`, `desvincularEnfermedad`) | IDOR: cualquier usuario con permiso `historias` abría la historia clínica de cualquier paciente cambiando el ID. Además, `show` creaba una historia nueva con el visitante como psicólogo dueño, y `update` sobrescribía segmentos de cualquier historia por ID | `autorizarPaciente()`: solo superadmin o un psicólogo con cita o historia con ese paciente. `update` solo acepta segmentos de la historia del paciente de la URL |
| H-05 | Alta | Corregido | `app/Http/Controllers/Admin/Configuracion/EmpleosController.php` (`update`) | Escalada de privilegios: un usuario no superadmin con permiso `empleados.editar` podía asignarse (o asignar) el rol Administrador sin llave maestra, enviando el formulario sin `modificar_seguridad` | Solo un superadmin puede otorgar o retirar roles de superadmin (403 en caso contrario) |
| H-06 | Alta | Mitigado | `routes/api.php`, `app/Http/Middleware/VerificarPermisoApi.php` | Cualquier token Sanctum (incluido el de un paciente) podía crear, editar, activar y eliminar marcas, modelos, combustibles y vehículos | La escritura exige el permiso de la vista (`bus_marcas`, `bus_modelos`, `bus_tipo_combustibles`, `bus_vehiculos`). **Pendiente:** la lectura sigue abierta a cualquier token para no romper la app móvil; revisarla cuando se definan los roles de la app |
| H-07 | Alta | Corregido | `resources/views/admin/psicologia/maestros/agenda/index.blade.php` | XSS almacenado: `motivo_rechazo_propuesta` (lo escribe el paciente) se insertaba con `innerHTML` en la agenda del psicólogo. También `cita.paciente` sin escapar | `Utils.escapeHtml(...)` en ambos |
| H-08 | Alta | Corregido | `app/Providers/AppServiceProvider.php` | `boot()` ejecutaba `migrate --force` y `db:seed --force` en peticiones web si no existía la tabla `sessions`. Un fallo de conexión o una base vacía en producción disparaba migraciones y seeders (con contraseñas conocidas) desde cualquier visita | Eliminado. Las migraciones se ejecutan solo con artisan |
| H-09 | Alta | Corregido | `LoginController`, `AdminMasterKeyController`, `PasswordRecoveryController`, `AuthApiController` | Sin límite de intentos: fuerza bruta de contraseñas, llave maestra y preguntas de seguridad (web y API) | Fase 2 (bloqueo temporal y permanente, límite por IP para usernames inexistentes) |
| H-10 | Media | Corregido | `routes/web.php` (`productos.actualizar.tasa`) | `POST /admin/maestros/productos/actualizar-tasa` estaba fuera de `auth`: cualquier visitante disparaba la consulta externa y recalculaba precios | `auth` + `CheckMenuPermission` (vista `productos`). Se verificó que la ruta no se usa en ninguna vista |
| H-11 | Media | Verificado | `routes/web.php`, `AdminMasterKeyController` | `admin/master-key` y `admin/master-key/verify` son públicas | No son abusables sin sesión: exigen `pending_admin_id`, que solo se fija tras validar la contraseña. Ahora además cuentan intentos y bloquean. Se corrigió la redirección del login de administradores, que apuntaba a la ruta protegida `admin.configuracion.master_key.form` en vez de `admin.master_key.form` |
| H-12 | Media | Verificado | `app/Http/Controllers/Auth/RegisterController.php` | `register` es público | Si ya existe un Administrador, un visitante recibe 403. El rol solo se elige cuando un administrador autenticado registra a alguien. **Riesgo residual:** en una base sin ningún Administrador, el primer visitante que se registre queda como Administrador; no desplegar nunca sin ejecutar los seeders |
| H-13 | Media | Corregido | `EmpleosController::update` | Doble hash: `Hash::make()` + el mutator del modelo; el usuario no podía entrar tras cambiar su contraseña | Se asigna texto plano |
| H-14 | Media | Corregido | `app/Http/Controllers/beca/SolicitudBecaController.php` (`store`) | La ruta es libre y `id_persona` venía del request: cualquier usuario creaba solicitudes de beca a nombre de otra persona | Si el usuario no tiene `crear` en `solicitud`/`solicitudes_becas`, se fuerza su propio `id_persona` |
| H-15 | Media | Corregido | `app/Http/Controllers/salud/PublicacionController.php` | Cualquier psicólogo podía editar o borrar publicaciones de otro | Solo el autor o un superadmin |
| H-16 | Media | Corregido | `app/Http/Controllers/salud/AgendaController.php` (`pendingList`, `dailyCitas`, `exportarPdf`) | Un psicólogo podía ver la agenda de otro pasando `psicologo_id`; `dailyCitas` no verificaba el rol | `psicologo_id` solo se acepta de superadmins; `dailyCitas` exige rol psicólogo |
| H-17 | Media | Corregido | `CitaController::historyJson` | El historial del paciente incluía las notas clínicas completas del psicólogo | Solo se envía `avance_estado`, que es lo único que muestra la vista |
| H-18 | Media | Corregido | `Api\BusViajePasajeroApiController::registrar` | Con `metodo=manual`, cualquier token registraba cualquier cédula en cualquier viaje y la respuesta revelaba el nombre de la persona | Solo el conductor del viaje |
| H-19 | Media | Corregido | `app/Models/Usuario.php` | `security_questions` (con los hash de las respuestas) se serializaba en respuestas JSON de la API que incluyen al conductor | Agregado a `$hidden` junto con `remember_token` |
| H-20 | Media | Corregido | `components/producto-show.blade.php`, `livewire/counter.blade.php` | XSS almacenado: `{!! $producto->descripcion !!}` sin sanear | Sanitizador propio por lista blanca `App\Support\Seguridad\HtmlSeguro` (sin dependencias nuevas) |
| H-21 | Media | Corregido | Vistas de marcas, modelos, combustibles, categorías y envases | `onclick='{!! ... json_encode($modelo) !!}'`: un nombre con `'` rompía el atributo e inyectaba eventos | `JSON_HEX_APOS \| JSON_HEX_TAG \| JSON_HEX_AMP` |
| H-22 | Media | Corregido | `resources/views/layouts/app.blade.php` | Los toasts de SweetAlert usaban `html:` con mensajes que incluyen datos del usuario o `$e->getMessage()` | `text:` |
| H-23 | Media | Mitigado | `bootstrap/app.php` (`trustProxies(at: '*')`) | Cualquier cliente puede falsificar `X-Forwarded-For`: la IP de la bitácora y los límites por IP pierden fiabilidad | Configurable con `SEGURIDAD_PROXIES_CONFIABLES`. **Pendiente:** fijarla a la IP real del proxy en producción |
| H-24 | Media | Mitigado | `.env.exmple` | Archivo versionado con credenciales reales (contraseña de BD, root de MySQL, SMTP de Mailtrap) | Renombrado a `.env.example` (el nombre que espera `composer.json`) y vaciado de secretos. **Pendiente:** rotar esas credenciales; siguen en el historial de git |
| H-25 | Media | Mitigado | `PasswordRecoveryController::postEmail` | Enumeración de usuarios ("Usuario no encontrado") | Mensaje genérico y límite por IP. Riesgo residual inherente al diseño: si el usuario existe, se muestran sus preguntas |
| H-26 | Baja | Corregido | `layouts/app.blade.php`, `HomeController::index` | Cada página volcaba en la consola del navegador los roles y permisos de sesión; cada visita a `/home` escribía en el log usuario, roles y permisos | Eliminados |
| H-27 | Baja | Corregido | `app/Models/Persona.php` | `dd($e->getMessage())` en un `catch`: mostraba el error SQL al usuario y cortaba la petición | `report($e)` |
| H-28 | Baja | Corregido | `app/Models/salud/Cita.php` (`orderByRaw`) | SQL armado con nombres de prioridades (creados por psicólogos) escapados con `addslashes` | Bindings `?` |
| H-29 | Baja | Corregido | `routes/web.php` | `admin.movimientos.registro_diario.index` definida dos veces. El chat tenía cuatro rutas repetidas con nombres distintos (`chat.contacts`, `chat.ping` duplicado, `chat.fetch` y `chat.store`, estas dos inalcanzables). Ningún nombre duplicado se usaba en vistas ni código | Duplicados eliminados. Las notificaciones usaban la ruta inexistente `admin.psicologia.maestros.chat.index`; ahora usan `chat.index` |
| H-30 | Baja | Corregido | `config/menu_routes.php`, `config/permisos.php` | Rutas de `/admin` sin vista registrada (`media.*`, ubicaciones, reportes, estadísticas de viajes, `archivos/ver`) devolvían 403 a todo usuario no superadmin. Con el bloqueo por 403 repetidos, cargar imágenes del mural podía bloquear cuentas legítimas | Rutas mapeadas a sus vistas; `media.*` como rutas libres para usuarios autenticados |
| H-31 | Baja | Verificado | `MediaController` | `media/*/{filename}` | No explotable (el parámetro no admite `/`). Igual se endureció con `basename` + `realpath` |
| H-32 | Info | Verificado | `bootstrap/app.php` | Excepciones CSRF | No hay ninguna; todas las rutas web validan CSRF |
| H-33 | Info | Verificado | `app/` | `whereRaw`/`DB::raw` sin bindings | No se encontró interpolación de datos del request |
| H-34 | Media | Pendiente | `CitaController::authorizeAccess`, `Cita::obtenerCitasGlobales` | Cualquier psicólogo ve citas **pendientes** de otros psicólogos, con datos personales del paciente (cédula, teléfono, discapacidad, motivo) | Parece una decisión de diseño (bandeja compartida). Confirmar con el equipo; si no es necesaria, exigir `psicologo_id` propio |
| H-35 | Media | Pendiente | `SolicitudBecasService` (líneas ~94 y ~320), `Livewire/Archivos.php` (~159) | Notas certificadas de becarios y archivos Excel importados (con cédulas) se guardan en el disco `public`, accesibles sin autenticación en `/storage/...` con nombres basados en `uniqid()` | Guardar en el disco `local` y servirlos con una ruta autenticada; mover los archivos existentes |
| H-36 | Baja | Pendiente | `HistoriaController::downloadZip` | El ZIP de expedientes se arma temporalmente en `storage/app/public` | Usar `storage_path('app/tmp')` |
| H-37 | Media | Pendiente | `ConsultaController` | Cualquier usuario con permiso `consultas` ve y edita cualquier consulta médica; `medico_id` es elegible | Decidir si cada médico solo edita sus consultas (`medico_id` apunta a `persona`) |
| H-38 | Baja | Pendiente | `routes/api.php` (`viajes/{viaje}/posicion`) | Endpoint público con posición GPS, pasajeros y estado de cualquier viaje (IDs secuenciales) | Moverlo bajo `auth:sanctum` o agregar `throttle` |
| H-39 | Baja | Pendiente | `CitaController::store`, `storeCampoAjax` | El paciente puede fijar la prioridad de su cita; `storeCampoAjax` no exige rol psicólogo | `unset($validated['prioridad'])` para pacientes; `abort_unless(tieneRol('psicologo'))` |
| H-40 | Baja | Pendiente | `app/Models/Usuario.php` | `$fillable` incluye `password`, `master_key` y `security_questions` | No hay asignación masiva real desde el request; quitarlos de `$fillable` cuando se refactorice `AuthService::register` |
| H-41 | Baja | Pendiente | `public/diagrama.dbml`, `public/hot` | El esquema de la base se sirve públicamente | Moverlo fuera de `public/` (por ejemplo a `docs/`) |
| H-42 | Baja | Pendiente | Varios controladores | Se muestra `$e->getMessage()` al usuario (filtra SQL) | Usar `report($e)` y un mensaje genérico, como ya hace `ConsultaController` |
| H-43 | Baja | Pendiente | `.env` | `APP_DEBUG=true`, `LOG_LEVEL=debug` | Correcto en desarrollo; en producción `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning` |
| H-44 | Baja | Pendiente | Varios | Funcionales detectados durante la revisión (sin riesgo de seguridad): `CitaController::cancel` impide cancelar al paciente; rutas `citas/{id}/edit` y `PUT citas/{id}` apuntan a métodos inexistentes; `MasterKeyController` compara `$user->role`, que no existe, y siempre responde 403 | Corregir en una tarea funcional aparte |

### Cabeceras de seguridad

`App\Http\Middleware\CabecerasSeguridad` (global) agrega:

- `X-Frame-Options: SAMEORIGIN`.
- `X-Content-Type-Options: nosniff`.
- `Referrer-Policy: strict-origin-when-cross-origin`.
- `Permissions-Policy: camera=(), microphone=(), payment=(), usb=(), geolocation=(self)`.
- `Content-Security-Policy-Report-Only`, con los orígenes que usa hoy el sistema: jsdelivr (SweetAlert2), cdnjs (Font Awesome), cdn.ckeditor.com, fonts.bunny.net, Pusher y el servidor de Vite.

La CSP queda solo como informe: el navegador avisa en la consola qué bloquearía, pero no bloquea nada. Antes de pasarla a modo estricto (`Content-Security-Policy`) hay que quitar los scripts y estilos en línea, o usar nonces.

## 5. Decisiones tomadas sin consultar

| ID | Decisión | Motivo |
|---|---|---|
| D-01 | El proyecto es **Laravel 12** (no 10 como dice `CLAUDE.md`): no existen `app/Http/Kernel.php` ni `app/Exceptions/Handler.php`. Los middlewares y el manejo de excepciones se registraron en `bootstrap/app.php`, y el listener Eloquent en un proveedor nuevo `SeguridadServiceProvider` | Es la forma soportada en Laravel 12 |
| D-02 | Base de pruebas `bienestraestudiantil_test` creada con root, con permisos para el usuario `admin`. Además se ejecutó `SET PERSIST log_bin_trust_function_creators = 1` en MySQL y se agregó `--log-bin-trust-function-creators=1` al servicio `mysql` de `docker-compose.yml` | Con el binlog activo, MySQL 8 exige el privilegio SUPER para crear triggers. La migración no falla si no puede crearlos: deja una advertencia en consola y en el log, y la inmutabilidad queda solo a nivel de modelo |
| D-03 | Las pruebas nuevas usan `DatabaseTransactions` y una migración incremental única por corrida, en vez de `RefreshDatabase` | En este equipo `migrate:fresh` tarda de 11 a 25 minutos (algunas tablas tardan más de 30 s cada una). Con transacciones la suite de seguridad corre en unos 30 s |
| D-04 | El módulo de cada evento HTTP se infiere primero de la vista de la ruta (`MapaRutas` + grupos de `menu_permissions`) y se usa `session('modulo_activo')` como desempate o respaldo | El módulo activo de la sesión no siempre corresponde a la pantalla visitada (por ejemplo, un superadmin en "comedor" abriendo la bitácora) |
| D-05 | La columna `accion` de una petición HTTP guarda la acción resuelta por `ResolutorAccion` (`ver`, `crear`, `editar`...) | Coincide con los ejemplos de `diagrama.dbml` y con el sistema de permisos; las peticiones se distinguen de los cambios de datos porque traen `metodo_http` y no `tabla_afectada` |
| D-06 | `session_id` se guarda como SHA-256 | Ver sección 2.1 |
| D-07 | Los valores de texto de antes/después se recortan a 1000 caracteres y se descartan los campos sensibles (`password`, `master_key`, `security_questions`, `remember_token` y cualquier campo con `password`, `token`, `secret`, `master_key` o `security_question` en el nombre). Si solo cambió un campo sensible, se registra el evento con la observación "Campos sensibles modificados (valor omitido)" | Evita duplicar textos clínicos largos y nunca guardar secretos, sin perder la evidencia de que hubo un cambio |
| D-08 | No se auditan cambios Eloquent mientras corren `migrate*` o `db:seed`. Los contadores de bloqueo se guardan con `saveQuietly()` | Las cargas iniciales no son actividad de usuarios. Los cambios de contadores ya quedan como eventos explícitos (`login_fallido`, `bloqueo_temporal`...), y registrarlos también como "actualizar usuario" duplicaría la bitácora |
| D-09 | Se agregó la columna `ventana_bloqueos_desde` (no estaba en la lista). La ventana de 24 h es fija desde el primer bloqueo temporal | Con solo un contador no se puede saber si los bloqueos ocurrieron dentro de la ventana |
| D-10 | El tercer intento fallido ya responde con el mensaje de bloqueo ("Podrás intentarlo de nuevo en 5 minutos") | Retroalimentación inmediata. Usernames inexistentes reciben exactamente la misma secuencia de mensajes |
| D-11 | Para usernames inexistentes: `RateLimiter` por IP + username (3 intentos / 5 min) y, al acumular 3 bloqueos en 24 h, el mismo mensaje de "cuenta bloqueada" durante 30 días (en caché). Además se compara la contraseña contra un hash ficticio para igualar el tiempo de respuesta | Que el bloqueo no sirva para enumerar usuarios. Riesgo residual: una cuenta real bloqueada lo está desde cualquier IP, mientras que un username inexistente solo desde la IP que lo intentó |
| D-12 | Mientras una cuenta está bloqueada no se evalúa la contraseña | Impide seguir probando contraseñas durante el bloqueo |
| D-13 | Un bloqueo temporal también cierra las sesiones activas de esa cuenta en la siguiente petición | Lo pide la Fase 2 ("si el usuario quedó bloqueado mientras navegaba"); aplica a ambos tipos de bloqueo |
| D-14 | Los 403 de usuarios no autenticados se registran pero no se cuentan. Tampoco cuentan los 403 de peticiones de "ruido" (polling, medios) | No hay a quién bloquear; y una imagen o un ping que falle en segundo plano no debe bloquear a un usuario legítimo |
| D-15 | `RequireMasterKey` redirige al formulario de llave maestra (no responde 403), así que no suma al contador. Se agregó que recuerde la URL pedida para volver a ella tras validar la llave | Su comportamiento original es redirigir; cambiarlo a 403 rompería el flujo |
| D-16 | Recuperación de contraseña: las cuentas bloqueadas no pueden recuperarse; el formulario para cambiar la contraseña caduca a los 10 minutos de responder; el reseteo de llave maestra solo se permite a administradores; las preguntas se aceptan en los dos formatos guardados en la base (`[{question, answer}]` y `pregunta_1/respuesta_1`) | Una recuperación no debe saltarse un bloqueo. El segundo formato lo genera `EmpleosController` y hacía fallar la recuperación con error 500 |
| D-17 | La API de catálogos de transporte solo exige permiso para escribir | No se conocen los roles de la app móvil; restringir la lectura podría dejarla sin funcionar |
| D-18 | `SESSION_LIFETIME` recomendado en 20 min, no en 15 | Ver sección 3 |
| D-19 | Cuando vence el tiempo, el aviso recarga la página (GET) y el middleware cierra la sesión y redirige al login con el mensaje. Antes de mostrar el aviso consulta al servidor cuánto tiempo queda | Otra pestaña o una petición AJAX pudo renovar la actividad; así el aviso no aparece por error |
| D-20 | La exportación PDF de la bitácora se limita a 1000 filas (configurable); la CSV no tiene límite y se genera por lotes. Las celdas que empiezan con `=`, `+`, `-`, `@` se prefijan con `'` | Memoria de dompdf; inyección de fórmulas en Excel |
| D-21 | El botón "Desbloquear" exige la acción `activar` sobre la vista `seguridad_bloqueos` (`rutas_accion` en `config/permisos.php`) | Desbloquear equivale a reactivar una cuenta |
| D-22 | No se modificó el `.env` real ni se le agregaron variables; todos los valores tienen un valor por defecto | Evita tocar la configuración del desarrollador; `.env.example` documenta las variables |
| D-23 | El sanitizador de HTML (`HtmlSeguro`) es propio, con `DOMDocument` y una lista blanca, en vez de instalar `mews/purifier` | Regla de evitar dependencias nuevas |
| D-24 | La vista 403 usa `@vite` para Tailwind. En el entorno actual Vite corre en modo desarrollo (`public/hot`), así que funciona; **para producción hay que ejecutar `npm run build`** porque la vista 403, la bitácora, los bloqueos y el aviso de inactividad usan clases de Tailwind nuevas | Consistencia con el resto de la UI, sin Bootstrap |
| D-25 | Se conservó `trustProxies('*')` como valor por defecto | Cambiarlo sin conocer la infraestructura podría romper la detección de IP detrás de un proxy |
| D-26 | Commits por fase: los archivos compartidos (`bootstrap/app.php`, `routes/web.php`, `config/*.php`, sidebar, layout, `AccesosDenegados`, `EmpleosController`, `Usuario`) se dejaron en el índice con versiones intermedias, para que cada commit contenga solo lo de su fase. Excepciones: el commit de la Fase 2 incluye la corrección H-01 (reescritura completa de `PasswordRecoveryController`), el registro de `ultima_actividad` al iniciar sesión (Fase 4) y el registro del rol asignado en `RegisterController` (Fase 1), porque están en los mismos métodos reescritos. Las pruebas de las Fases 3 a 5 van en el commit de la Fase 7 | Requisito de commits pequeños por fase |

## 6. Cómo probar manualmente cada mecanismo

Usuarios de prueba de los seeders (contraseña `12345678`, llave maestra `masterkey123`): `admin@example.com` (Administrador), `argenisdoctor@gmail.com` (Doctor), `america@example.com` (Paciente).

Abre el sistema en `http://localhost:8000`. Para revisar la bitácora entra como administrador en **Administración del sistema → Gestión de usuarios → Bitácora de auditoría**. La primera vez pedirá la llave maestra.

### 6.1 Bitácora

1. Inicia sesión como `argenisdoctor@gmail.com`, navega un par de pantallas y cierra sesión.
2. Entra como administrador a la bitácora y filtra por usuario `argenisdoctor`. Deben aparecer `login_exitoso`, las peticiones `ver` con su ruta y código 200, y `logout`.
3. Edita un rol (por ejemplo, agrega un permiso) y filtra por acción `rol_permisos_actualizados`. Abre el detalle (ojo): verás "Cambios: antes → después" con los permisos otorgados o quitados.
4. Edita una sede y abre el evento `actualizar` de la tabla `sede`: el detalle muestra solo los campos que cambiaron, con el valor anterior en rojo y el nuevo en verde.
5. Cambia la contraseña de un empleado desde **Empleados → editar → modificar seguridad**. El evento `actualizar` de `usuario` **no** debe mostrar la contraseña; su observación dice "Campos sensibles modificados (valor omitido): password".
6. Pulsa **Exportar CSV** y **Exportar PDF**. Se descargan los archivos, y en la bitácora aparece `bitacora_exportada`.
7. Prueba de inmutabilidad: ejecuta las consultas de la sección 6 de `EVIDENCIAS_AUDITORIA.md`.

### 6.2 Bloqueo por contraseña

1. En `/login` escribe `argenisdoctor@gmail.com` con una contraseña incorrecta dos veces: "Credenciales inválidas.".
2. Tercer intento incorrecto: "Demasiados intentos fallidos. Podrás intentarlo de nuevo en 5 minutos.".
3. Con la contraseña correcta, antes de 5 minutos: el mismo mensaje de bloqueo.
4. Pasados 5 minutos, la contraseña correcta funciona.
5. Repite los pasos 1 y 2 tres veces en menos de 24 horas: la cuenta queda bloqueada de forma permanente ("Tu cuenta está bloqueada. Comunícate con un administrador...") y ya no entra ni con la contraseña correcta.
6. Usuario inexistente: repite con `noexiste@example.com`. Los mensajes son idénticos (incluido el bloqueo al tercer intento).
7. Para no esperar 5 minutos en las pruebas: `SEGURIDAD_MINUTOS_BLOQUEO_TEMPORAL=1` en `.env` y `php artisan optimize:clear`.

### 6.3 Llave maestra

1. Entra como `admin@example.com` y abre **Empleados**. El sistema pide la llave maestra.
2. Escribe tres llaves incorrectas: la sesión se cierra y la cuenta queda con bloqueo temporal (motivo "Llave maestra incorrecta").

### 6.4 Recuperación de contraseña

Solo `admin@example.com` tiene preguntas de seguridad en los seeders (respuesta correcta: `example` en ambas). Para probar con otra cuenta, configúralas desde **Empleados → editar → modificar seguridad**.

1. En `/password/recover` escribe `admin@example.com`. Aparecen las dos preguntas.
2. Responde mal tres veces: la cuenta queda con bloqueo temporal y se vuelve al formulario inicial.
3. Desbloquea la cuenta con el comando de emergencia (6.7) y responde bien: aparece la elección entre restablecer contraseña o llave maestra.
4. Para comprobar la corrección de H-01: tras el paso 1, sin responder, envía un POST a `/password/recover/reset-password` (por ejemplo desde las herramientas del navegador). Debe redirigir al formulario inicial sin cambiar la contraseña.

### 6.5 Accesos restringidos (403)

1. Entra como `america@example.com` (Paciente) y abre `http://localhost:8000/admin/configuracion/roles`.
2. La página 403 dice "Te quedan 9 intentos antes de que tu cuenta sea bloqueada...". Recarga y el número baja.
3. Al décimo intento en menos de 10 minutos, la sesión se cierra y el login muestra el mensaje de cuenta bloqueada. En la bitácora: diez `acceso_denegado` y un `bloqueo_permanente` de nivel crítico.

### 6.6 Inactividad

1. Para probar rápido: `SEGURIDAD_MINUTOS_INACTIVIDAD=2` en `.env` y `php artisan optimize:clear`.
2. Inicia sesión y no toques nada. Un minuto antes de vencer aparece el aviso "Tu sesión está por expirar" con cuenta regresiva.
3. Pulsa **Seguir conectado**: el aviso desaparece y el contador se reinicia.
4. Déjalo vencer: la página recarga, vuelve al login con "Tu sesión se cerró por inactividad" y en la bitácora queda `sesion_expirada`.
5. Abre el chat y déjalo abierto sin escribir: el polling del chat **no** evita que la sesión expire.

### 6.7 Administración de bloqueos

1. Como administrador: **Gestión de usuarios → Bloqueos de seguridad**. Aparecen las cuentas bloqueadas con tipo, motivo, fecha, intentos y último login.
2. Pulsa la fila y luego el candado (Desbloquear); confirma. La cuenta desaparece de la lista y en la bitácora queda `desbloqueo` con tu usuario como responsable.
3. Tu propia cuenta muestra "Tu cuenta" y no tiene botón de desbloqueo.
4. Emergencia (si se bloquea el último administrador):

```bash
docker exec -it SisgestiondeBienestraEstudiantil php artisan seguridad:desbloquear admin@example.com
```

En la bitácora queda `desbloqueo` sin usuario y con observación `consola: ...`.

### 6.8 Cabeceras

```bash
curl -sI http://localhost:8000/login | grep -iE "x-frame|x-content|referrer|permissions|content-security"
```

## 7. Comandos para verificar

Todos se ejecutan dentro del contenedor:

```bash
# Sintaxis de los archivos PHP tocados en la rama (78745c8 es el commit donde nace la rama)
docker exec SisgestiondeBienestraEstudiantil sh -c 'for f in $(git -c safe.directory=/var/www diff --name-only --diff-filter=AM 78745c8 -- "*.php"); do php -l "$f"; done'

# Rutas (debe listar sin errores; incluye bitácora y bloqueos)
docker exec SisgestiondeBienestraEstudiantil php artisan route:list --path=admin/configuracion

# Base de desarrollo desde cero (tarda de 15 a 25 minutos en este equipo)
docker exec SisgestiondeBienestraEstudiantil php artisan migrate:fresh --seed

# Catálogo de permisos (crea las vistas bitacora y seguridad_bloqueos)
docker exec SisgestiondeBienestraEstudiantil php artisan permisos:sincronizar
docker exec SisgestiondeBienestraEstudiantil php artisan optimize:clear

# Pruebas de seguridad (base bienestraestudiantil_test; unos 30 s)
docker exec SisgestiondeBienestraEstudiantil php artisan test tests/Feature/Seguridad

# Suite completa (las pruebas antiguas de becas fallan desde antes; ver sección 1)
docker exec SisgestiondeBienestraEstudiantil php artisan test

# Triggers instalados
docker exec mysqlbienestraestudiantil sh -c 'mysql -uadmin -p"$MYSQL_PASSWORD" bienestraestudiantil -e "SHOW TRIGGERS"'

# Comando de emergencia
docker exec SisgestiondeBienestraEstudiantil php artisan seguridad:desbloquear --help
```

Si se recrea el contenedor de MySQL **sin** volumen y sin el flag de `docker-compose.yml`, ejecutar una vez como root, antes de migrar:

```sql
SET PERSIST log_bin_trust_function_creators = 1;
```

## 8. Lista de commits de la rama

| Commit | Fase | Mensaje |
|---|---|---|
| `a372ebe` | Fase 0 | Prepara reconocimiento de seguridad y base de datos de pruebas aislada |
| `58aaf12` | Fase 1 | Agrega bitácora de auditoría inmutable |
| `18c84fa` | Fase 2 | Agrega bloqueo por credenciales incorrectas |
| `18e5601` | Fase 3 | Bloquea cuentas por accesos repetidos a vistas restringidas |
| `eeaeaeb` | Fase 4 | Cierra la sesión por inactividad |
| `241cdfd` | Fase 5 | Agrega administración de bloqueos de seguridad |
| `dc5b750` | Fase 6 | Corrige vulnerabilidades detectadas en la revisión |
| `17e0d7c` | Fase 7 | Agrega pruebas de accesos denegados, inactividad y administración de bloqueos |
| (este commit) | Fase 8 | Agrega reporte de seguridad y evidencias de auditoría |

Rama creada desde `78745c8`. No se hizo push. Para ver el detalle: `git log --stat 78745c8..feature/seguridad-auditoria`.
