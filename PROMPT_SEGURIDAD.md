Vas a trabajar solo durante la noche, sin nadie que responda preguntas. Lee primero `CLAUDE.md` completo y respétalo en todo momento.

AUTORIZACIÓN PERMANENTE PARA ESTA SESIÓN: soy el dueño del proyecto y autorizo de forma permanente, sin volver a preguntarme, lo siguiente:
- Ejecutar cualquier comando "docker exec SisgestiondeBienestraEstudiantil ..." y "docker exec mysqlbienestraestudiantil ...".
- Ejecutar "php artisan migrate:fresh --seed", "migrate", "db:seed", "permisos:sincronizar", "optimize:clear", "route:list" y "test" dentro del contenedor. La base de datos es de desarrollo y puede borrarse y recrearse cuantas veces haga falta.
- Crear la base de datos de pruebas "bienestraestudiantil_test" en el contenedor de MySQL.
- Crear, editar y eliminar archivos dentro del proyecto, crear ramas y hacer commits en la rama feature/seguridad-auditoria.
No está autorizado: hacer push, tocar archivos fuera del proyecto, ni modificar el .env real salvo para agregar variables nuevas al final.

# Objetivo

Endurecer la seguridad del sistema y dejar evidencia lista para auditorías. Esto incluye:

1. Bitácora de auditoría de toda interacción con el sistema.
2. Bloqueo por contraseña incorrecta: timeout temporal y luego bloqueo permanente.
3. Bloqueo por intentos repetidos de entrar a vistas restringidas.
4. Cierre de sesión por inactividad.
5. Pantallas de administración para consultar la bitácora y desbloquear usuarios.
6. Revisión de vulnerabilidades del código, corrigiendo las claras y documentando el resto.
7. Pruebas automatizadas y un reporte final.

# Reglas de trabajo autónomo

- **No te detengas a preguntar.** Ante una ambigüedad, toma la decisión más razonable y segura, aplícala y regístrala en la sección "Decisiones tomadas" del reporte final.
- Antes de empezar, crea la rama `feature/seguridad-auditoria` desde la rama actual. Haz un commit al terminar cada fase. No hagas push.
- Usa siempre `docker exec` para artisan, composer y phpunit (ver `CLAUDE.md`).
- Evita dependencias nuevas de Composer. Si una es indispensable, intenta instalarla. Si la red falla, implementa una alternativa propia y anótalo.
- Si una fase se bloquea, documenta exactamente por qué en el reporte y continúa con la siguiente. No la dejes a medias en un estado que rompa la aplicación.
- Después de cada fase:
  - `php -l` de los archivos tocados.
  - `php artisan route:list` debe funcionar.
  - `php artisan migrate:fresh --seed` debe terminar sin errores.
- No modifiques el flujo de redirección inicial (`ModuleFilter::resolveInitialRoute`) ni la lógica de módulos. Solo agrega lo necesario alrededor.
- Todo valor configurable va en un archivo nuevo `config/seguridad.php`, con valores por defecto leídos de `.env`. Documenta en el reporte cada variable nueva que haya que agregar a `.env`.

# Fase 0 — Reconocimiento

1. Lee y entiende antes de escribir código:
   - `routes/web.php`, `routes/psicologia.php`, `routes/transporte.php`, `routes/beca.php`.
   - `app/Http/Kernel.php`, `app/Http/Middleware/*`, `app/Exceptions/Handler.php`.
   - `app/Http/Controllers/Auth/*`, el modelo `Usuario`, `config/session.php`, `config/auth.php`, `phpunit.xml`.
   - `AppServiceProvider`, `ModuleFilter`, `CheckMenuPermission`, `RequireMasterKey`, `PasswordRecoveryController`, `EmpleosController`.
2. Guarda la salida de `php artisan route:list` en `docs/seguridad/rutas_inicial.txt`.
3. Decide la base de datos de pruebas:
   - Si puedes crear `bienestraestudiantil_test` en el contenedor MySQL (usuario root o el de `.env`), úsala en `phpunit.xml` o `.env.testing`.
   - Si no, usa la base de desarrollo con `RefreshDatabase` y anótalo.

# Fase 1 — Bitácora de auditoría

## Tabla
Crea una migración nueva para la tabla `bitacora` basada en el diseño de `diagrama.dbml` (descrito en `CLAUDE.md`).

**Columnas mínimas:**
- `id`, `modulo`, `accion`, `tabla_afectada`, `registro_id`, `usuario_id` (nullable, FK a `usuario.id_usuario` con `nullOnDelete`), `nombre_usuario`.
- `datos_anteriores` (json), `datos_modificados` (json).
- `ip_address`, `navegador`, `sistema_operativo`, `fecha_hora`.

**Columnas que debes agregar y justificar en el reporte frente a `bitacora_sistema` del profesor:**
- `metodo_http`, `ruta`, `url`, `codigo_respuesta`, `nivel` (info / advertencia / critico), `session_id` y `observacion`.

**Índices:** `usuario_id`, `modulo`, `accion`, `fecha_hora`, `ip_address`.

**Solo INSERT, a nivel de base de datos:** en la misma migración crea triggers MySQL `BEFORE UPDATE` y `BEFORE DELETE` que lancen `SIGNAL SQLSTATE '45000'`. Esto sirve como evidencia de inmutabilidad. El `down()` elimina los triggers antes que la tabla.

## Modelo y servicio
- **Modelo `App\Models\Bitacora`:** sin `updated_at`. Sobrescribe `save()` en registros existentes y `delete()` para que lancen excepción.
- **Servicio `App\Support\Auditoria\Bitacora`** con un método estático `registrar(...)`:
  - Toma automáticamente usuario, IP, navegador y SO del request actual.
  - Parsea el User-Agent con una clase propia simple (navegador + versión, SO + versión). Prohibido que una falla al registrar rompa la petición: envuélvelo en try/catch y escribe en `Log` si falla.

## Qué se registra
1. **Peticiones HTTP de usuarios autenticados.**
   - Middleware terminable (`terminate()`), registrado en el grupo `web`. Guarda método, ruta, URL sin query string sensible, código de respuesta y módulo (el `session('modulo_activo')` o el inferido de la vista según `MapaRutas`).
   - Excluye por configuración el polling y el ruido: `chat.ping`, `chat.contactos`, `chat.contacts`, `chat.fetch`, `chat.mensajes`, `notifications.*`, `broadcasting/auth`, `media.*`, assets y peticiones `HEAD`/`OPTIONS`.
   - No guardes cuerpos de petición con contraseñas, llaves maestras ni respuestas de seguridad.
2. **Cambios de datos (crear, actualizar, eliminar, restaurar) de todos los modelos Eloquent.**
   - Usa un listener global de eventos `eloquent.*` o un trait aplicado de forma centralizada.
   - Guarda antes y después solo con los campos que cambiaron.
   - Excluye siempre `password`, `master_key`, `security_questions`, `remember_token` y cualquier campo con `password`/`token`/`secret` en el nombre.
   - Excluye el propio modelo `Bitacora`.
   - Para cambios hechos con `DB::table()` (pivotes de permisos, roles, módulos), registra manualmente en los controladores de roles, permisos y empleados: qué se otorgó y qué se quitó.
3. **Eventos de autenticación:**
   - Login exitoso y fallido (con el username intentado, sin la contraseña) y logout.
   - Bloqueo temporal, bloqueo permanente y desbloqueo.
   - Llave maestra correcta e incorrecta.
   - Recuperación de contraseña: preguntas correctas o incorrectas, reseteo de contraseña y reseteo de llave maestra.
   - Sesión expirada por inactividad.
4. **Cada respuesta 403** (`acceso_denegado`), con la vista y la acción que se intentó.

## Pantalla de bitácora
- Ruta `admin/configuracion/bitacora`, solo lectura, protegida por `CheckMenuPermission` y `RequireMasterKey`.
- Filtros: usuario, módulo, acción, nivel, IP y rango de fechas. Paginación.
- Detalle que muestre la diferencia antes → después.
- Exportación a CSV. También a PDF si el proyecto ya tiene una librería de PDF instalada; revísalo en `composer.json`.
- Registra la vista `bitacora` en `config/menu_permissions.php` (grupo `administracion`) y `config/menu_routes.php`, ejecuta `permisos:sincronizar` y agrega el enlace en el sidebar de administración con `@canMenu('bitacora')`.
- Exportar la bitácora también se registra en la bitácora.

# Fase 2 — Bloqueo por credenciales incorrectas

## Columnas nuevas en `usuario`
Crea una migración que agregue:
- `intentos_fallidos` (smallint, default 0), `bloqueado_hasta` (timestamp nullable), `bloqueos_temporales` (smallint, default 0).
- `bloqueado` (boolean, default false), `bloqueado_en`, `motivo_bloqueo`.
- `ultimo_login`, `ultimo_login_ip`, `intentos_llave_fallidos`.

En el reporte, justifica la equivalencia con `intentos_fallidos_use`, `bloqueado_hasta_use` y `ultimo_login_use` de la tabla `user` del profesor.

## Reglas (configurables)
| Variable | Valor por defecto | Significado |
|---|---|---|
| `max_intentos_login` | 3 | Contraseñas incorrectas seguidas antes del bloqueo temporal. |
| `minutos_bloqueo_temporal` | 5 | Duración del bloqueo temporal. |
| `bloqueos_temporales_para_permanente` | 3 | Bloqueos temporales en `horas_ventana_bloqueos` que causan bloqueo permanente. |
| `horas_ventana_bloqueos` | 24 | Ventana para contar bloqueos temporales. |

- Al llegar a 3 contraseñas incorrectas seguidas: bloqueo temporal de 5 minutos.
- Al acumular 3 bloqueos temporales en 24 horas: bloqueo permanente, que solo un administrador puede levantar.
- Un login exitoso reinicia `intentos_fallidos` y guarda `ultimo_login` y `ultimo_login_ip`.

## Detalles obligatorios
- El mensaje al usuario es genérico y no revela si el username existe. Durante un bloqueo temporal sí puede indicar cuántos minutos faltan.
- Para usernames inexistentes aplica también `RateLimiter` por IP + username, así el bloqueo no sirve para enumerar usuarios.
- Aplica esto en `LoginController` y en el flujo de administradores (`AdminMasterKeyController`).
- **Llave maestra:** 3 fallos (en `AdminMasterKeyController` y `EmpleosController::verifyMasterKey`) cierran la sesión y aplican bloqueo temporal con las mismas reglas.
- **Recuperación por preguntas de seguridad:** limita los intentos por usuario e IP. Al superar el límite, aplica bloqueo temporal. Registra todo.
- **Middleware `VerificarUsuarioBloqueado`** en el grupo `web` para usuarios autenticados. Si el usuario quedó bloqueado mientras navegaba: logout, invalida la sesión, regenera el token CSRF y redirige al login con el mensaje correspondiente.

# Fase 3 — Bloqueo por intentos a vistas restringidas

- Cuenta cada 403 por usuario en una ventana deslizante. Valores configurables: `max_accesos_denegados` = 10 en `minutos_ventana_accesos` = 10.
- Al superar el límite: bloqueo **permanente** con motivo `accesos_restringidos_repetidos`, logout y evento de nivel crítico en la bitácora.
- Cubre los 403 de `CheckMenuPermission`, `RequireMasterKey` y cualquier `abort(403)`/`AuthorizationException`. Centralízalo en el handler de excepciones o en un listener, no copiado en cada sitio.
- Los superadmins no reciben 403 de `CheckMenuPermission`, pero si les llega un 403 por otra vía también cuentan.
- La vista de error 403 debe estar en Tailwind (sin Bootstrap) y avisar cuántos intentos restan antes del bloqueo.

# Fase 4 — Cierre de sesión por inactividad

- Middleware `CerrarSesionPorInactividad` con `minutos_inactividad` configurable (por defecto 15).
  - Guarda `session('ultima_actividad')`.
  - Si se supera el tiempo: logout, invalida la sesión, registra `sesion_expirada` y redirige al login con un mensaje.
- **Las peticiones de polling no renuevan la actividad.** Usa la misma lista de exclusiones de la Fase 1; si no, la sesión nunca expira porque el chat hace ping constantemente.
- En el layout autenticado agrega un aviso en Tailwind + Alpine que aparezca 1 minuto antes de expirar, con el botón "Seguir conectado". Ese botón llama a una ruta ligera que sí renueva la actividad.
- Revisa `config/session.php` y documenta los valores recomendados para `.env`:
  - `SESSION_LIFETIME` alineado con la inactividad.
  - `http_only` true y `same_site` `lax` o `strict`.
  - `SESSION_SECURE_COOKIE` true en producción con HTTPS.
  - `SESSION_EXPIRE_ON_CLOSE`.
- Verifica que el login regenere el ID de sesión y que el logout la invalide.

# Fase 5 — Administración de seguridad

- Pantalla `admin/configuracion/seguridad/bloqueos`, protegida por `CheckMenuPermission` + `RequireMasterKey`:
  - Lista los usuarios con bloqueo temporal o permanente: motivo, fecha, intentos y último login.
  - Acción "Desbloquear": reinicia contadores y pide confirmación. Registra quién desbloqueó.
  - Registra la vista `seguridad_bloqueos` en el catálogo de permisos y en el sidebar de administración.
- Comando `php artisan seguridad:desbloquear {username}`: recuperación de emergencia si se bloquea el último administrador. También queda en la bitácora con usuario nulo y observación "consola".
- Un usuario no puede desbloquearse a sí mismo.

# Fase 6 — Revisión de vulnerabilidades

Revisa todo el código. Corrige lo que sea claro y de bajo riesgo de romper funcionalidad; documenta el resto con severidad (Crítica / Alta / Media / Baja), archivo, línea y recomendación.

**Hallazgos ya detectados en revisiones anteriores. Verifícalos y corrígelos:**
1. **Path traversal (Crítica):** la ruta `admin/configuracion/archivos/ver/{archivo}` en `routes/web.php` usa `->where('archivo', '.*')` y concatena `storage_path('app/public/' . $archivo)`.
   - Permite leer archivos fuera de la carpeta, por ejemplo `../../.env`.
   - Corrígelo con `realpath` y verificando que la ruta resuelta quede dentro de `storage/app/public`.
   - Revisa igual `MediaController` (`media/profile-photos/{filename}`, `media/publicaciones/{filename}`).
2. **Ruta sin autenticación:** `POST /admin/maestros/productos/actualizar-tasa` (`productos.actualizar.tasa`) está fuera del grupo `auth`.
3. **Rutas públicas:** `admin/master-key` y `admin/master-key/verify` están fuera de `auth`. Verifica en `AdminMasterKeyController` que no se puedan abusar sin sesión iniciada.
4. **Registro público:** `register` es público. Verifica qué rol asigna `RegisterController` y que no permita escalar privilegios ni elegir rol desde el request.
5. **Doble hash:** `EmpleosController::update` hace `$usuario->password = Hash::make(...)`, pero el modelo ya hashea en el mutator. La contraseña queda doblemente hasheada y el usuario no puede entrar. Asigna texto plano.
6. **Migraciones desde la web:** `AppServiceProvider::boot` ejecuta `migrate --force` y `db:seed --force` en peticiones web si no existe la tabla `sessions`. Elimínalo o restríngelo a `app()->environment('local')`, y documenta el riesgo.
7. **Código de depuración:** busca y elimina scripts como `list_roles.php` y rutas de prueba.
8. **IDOR (acceso a datos de otros por ID):** verifica que un usuario no pueda leer o modificar registros ajenos cambiando el ID en la URL, sobre todo en:
   - Citas y historias clínicas de psicología.
   - Chat (`/mensajes/{user}`).
   - Solicitudes de beca.
   - Consultas médicas y recetas.
   Un paciente solo debe ver lo suyo.
9. **Rutas duplicadas:** `admin.movimientos.registro_diario.index` está definida dos veces, y el chat tiene rutas repetidas con nombres distintos. Limpia sin romper las vistas que las usan; busca cada nombre con grep antes de quitarlo.

**Revisa además:**
- `$fillable`/`$guarded` en todos los modelos, sobre todo los campos de bloqueo y permisos.
- Uso de `{!! !!}` con datos de usuario (XSS).
- `whereRaw`/`DB::raw` sin bindings.
- Excepciones en `VerifyCsrfToken`.
- Validación de archivos subidos (mime, tamaño, nombre aleatorio, disco).
- `APP_DEBUG` y `APP_ENV` en `.env` y `.env.example`.
- Datos sensibles en logs.

**Cabeceras de seguridad:** agrega un middleware con `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin` y `Permissions-Policy` básica. Agrega una CSP solo en modo `Report-Only`, porque el sistema carga recursos de CDNs (SweetAlert2 desde jsdelivr, Font Awesome, etc.) y una CSP estricta los rompería.

# Fase 7 — Pruebas automatizadas

Crea pruebas Feature en `tests/Feature/Seguridad/`:
- 3 contraseñas incorrectas → bloqueo temporal. Tras el tiempo (usa `travel()`), puede volver a intentar.
- 3 bloqueos temporales en 24 h → bloqueo permanente. Con contraseña correcta tampoco entra.
- Username inexistente: mismo mensaje, y el rate limit por IP funciona.
- 10 accesos 403 en la ventana → usuario bloqueado y desconectado.
- Inactividad mayor al límite → logout. Las rutas de polling no renuevan la actividad.
- La bitácora registra login, logout, cambios de modelo sin campos sensibles y 403.
- `UPDATE`/`DELETE` sobre `bitacora` falla (trigger y modelo).
- El path traversal en `archivos/ver` devuelve 403 o 404.
- Un usuario sin el permiso `editar` recibe 403 en una ruta `*.update`, y con el permiso pasa.

Ejecuta toda la suite al final. Si alguna prueba falla y no logras corregirla, déjala marcada como `markTestIncomplete` con la razón y anótala en el reporte.

# Fase 8 — Reporte final

Crea `docs/seguridad/REPORTE_SEGURIDAD.md` con estas secciones:
1. **Resumen ejecutivo:** qué quedó implementado y qué no.
2. **Tablas y columnas nuevas:** comparación con `bitacora_sistema` y `user` del DER del profesor, con justificación de cada diferencia.
3. **Configuración:** variables de `config/seguridad.php` y lo que hay que agregar a `.env`.
4. **Hallazgos de vulnerabilidades:** tabla con severidad, estado (corregido / pendiente), archivo y recomendación.
5. **Decisiones tomadas sin consultar.**
6. **Cómo probar manualmente cada mecanismo**, paso a paso.
7. **Comandos para verificar** que todo funciona.
8. **Lista de commits** de la rama.

Crea también `docs/seguridad/EVIDENCIAS_AUDITORIA.md` con consultas SQL listas para mostrar en una auditoría:
- Intentos fallidos por usuario.
- Bloqueos.
- Accesos denegados por IP.
- Cambios a roles y permisos.
- Actividad de un usuario en un rango de fechas.
- Prueba de inmutabilidad de la bitácora.

Al terminar, deja el árbol de trabajo limpio (todo commiteado) y escribe en la terminal un resumen corto con la ruta del reporte.
