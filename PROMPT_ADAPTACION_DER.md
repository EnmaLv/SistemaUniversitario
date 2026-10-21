Vas a trabajar solo durante varias horas, sin nadie que responda preguntas. Antes de empezar, lee completos `CLAUDE.md`, `MAPEO_DER.md` y `DERProfesor.txt` (si `DERProfesor.txt` no está en la raíz, búscalo en el proyecto). También lee `docs/seguridad/REPORTE_SEGURIDAD.md`, que es el resultado del trabajo anterior.

# Objetivo

1. Corregir los pendientes de seguridad que quedaron abiertos (Fase 1).
2. Crear usuarios automáticamente para la comunidad universitaria (Fase 2).
3. Hacer el sistema multisede (Fase 3).
4. Implementar `inventario_total` (Fase 4).
5. Revisar qué tablas del modelado propio faltan por implementar (Fase 5).
6. Adaptar la base de datos a la nomenclatura del DER del profesor (Fase 6).
7. Entregar un reporte final (Fase 7).

# Reglas de trabajo autónomo

- No te detengas a preguntar. Ante una ambigüedad, toma la decisión más razonable y segura y anótala en "Decisiones tomadas" del reporte final.
- Crea la rama `feature/adaptacion-der` desde la rama actual. Haz un commit al terminar cada fase, o cada módulo en la Fase 6. No hagas push.
- Usa siempre `docker exec` para artisan, composer y phpunit (ver `CLAUDE.md`).
- La base de datos es de desarrollo: **puedes modificar las migraciones de creación existentes** en lugar de crear migraciones `ALTER` encima. Así el profesor ve un esquema limpio. `migrate:fresh --seed` debe funcionar al final de cada fase.
- Después de cada fase, todas estas verificaciones deben pasar antes de hacer commit:
  - `php -l` de los archivos tocados.
  - `php artisan route:list`.
  - `php artisan migrate:fresh --seed`.
  - `php artisan test`.
- Si una fase se bloquea, haz `git stash` o revierte los cambios de esa fase para dejar la rama funcionando. Documenta el motivo y pasa a la siguiente fase.
- No cambies el flujo de redirección inicial (`ModuleFilter::resolveInitialRoute`), el sistema de permisos granulares ni la bitácora. Solo adapta los nombres de tablas y columnas que ellos usan.
- Todo lo nuevo de seguridad y permisos debe quedar registrado en la bitácora, igual que ya ocurre con el resto del sistema.

# Compatibilidad con PostgreSQL (migración futura)

El sistema usa MySQL 8 + Laravel 12, pero se migrará a PostgreSQL más adelante. En todo lo que escribas o adaptes:
- Usa el Schema Builder y Eloquent/Query Builder. Evita SQL específico de MySQL: GROUP_CONCAT, IFNULL, backticks, ON DUPLICATE KEY, LIMIT en UPDATE/DELETE, comparaciones de texto que dependan de que MySQL no distinga mayúsculas, y ENUM nativos (usa string + validación).
- Si encuentras SQL crudo de MySQL en el código que tocas, reemplázalo por una versión portable o por una rama según el driver (DB::getDriverName()).
- Los triggers de inmutabilidad de la bitácora son de MySQL: déjalos detrás de una verificación del driver y escribe también la versión PostgreSQL (función plpgsql + trigger), aunque hoy solo se ejecute la de MySQL.
- La tabla `user` es palabra reservada en PostgreSQL. Deja siempre que el Query Builder ponga las comillas y nunca escribas su nombre a mano en SQL crudo.
- En la sección "Cambios que el profesor debe conocer" del reporte final, agrega una subsección "Preparación para PostgreSQL" con lo que quedó listo y lo que falta para migrar.

# Fase 1 — Pendientes de seguridad (decisiones ya tomadas por el dueño)

## 1.1 Confidencialidad en psicología
Por la confidencialidad profesional que exige la ley del ejercicio de la psicología:

- Solo un usuario con el rol **Psicólogo** (búscalo por slug/nombre; si no existe, créalo en el seeder de roles con los permisos del módulo `psicologia`) puede **gestionar** cualquier cosa del módulo de psicología: agenda, citas ajenas, historias clínicas, notas de evolución, plantillas, prioridades y horarios.
- Esta restricción aplica **aunque el usuario tenga los permisos de la vista** e incluso a los superadmins (Administrador y Secretaria de Bienestar). Un administrador puede gestionar el catálogo de vistas, pero no puede leer datos clínicos.
  - Implementa un middleware o *policy* específico del módulo que exija el rol Psicólogo, además de `CheckMenuPermission`.
- Cada psicólogo ve **solo lo suyo**: sus citas (pendientes, confirmadas e historial), sus pacientes y las historias clínicas donde él es `psicologo_id`. Ningún psicólogo puede ver las citas pendientes ni los datos de pacientes de otro.
  - Aplica esto en controladores, consultas, exportaciones PDF/Word/ZIP, endpoints JSON y estadísticas.
  - Las estadísticas generales solo pueden mostrar números agregados, sin datos identificables.
- Las vistas del **paciente** (sus propias citas, mural, estado de ánimo diario, solicitar cita) siguen disponibles para el rol Paciente y solo con sus datos.
- Escribe pruebas: un psicólogo A no ve ni modifica nada del psicólogo B, y un administrador no ve historias clínicas.

## 1.2 Archivos privados
Situación actual: los Excel importados en el apartado *Archivos* y las notas o documentos de becarios se guardan en el disco `public` (`storage/app/public`, enlazado a `public/storage`). Cualquiera que conozca o adivine la URL puede descargarlos sin iniciar sesión.

- Muévelos al disco privado (`local`, `storage/app/private` o el que corresponda a tu versión de Laravel).
- Sírvelos mediante un controlador que verifique sesión y permiso (`exportar` o `ver` de la vista correspondiente). El becario solo puede acceder a sus propios documentos.
- Migra los archivos existentes con un comando artisan idempotente que actualice las rutas guardadas en la base de datos.
- Revisa si queda algún otro dato sensible en el disco `public`. Lo único que debe quedar público son imágenes de productos, fotos de publicaciones del mural y recursos estáticos.

## 1.3 Consultas médicas
Se mantiene así por decisión del dueño: cualquier usuario con los permisos de la vista `consultas` puede gestionarlas. No cambies esto.

## 1.4 Otros pendientes
Revisa el resto de hallazgos pendientes de `REPORTE_SEGURIDAD.md`. Corrige los que no requieran una decisión de negocio y deja los demás documentados.

# Fase 2 — Cuentas automáticas para la comunidad universitaria

Al registrar una persona, manualmente (`PersonaController`) o por importación de Excel (apartado *Archivos*, `ArchivoController`), el sistema debe crearle su cuenta de usuario automáticamente.

**Reglas de la cuenta:**
- Usuario de login = **la cédula**, tal como la usa la intranet de la universidad. Normalízala igual que el login: sin puntos ni espacios; decide y documenta qué pasa con el prefijo V/E.
- Contraseña inicial = **la cédula**. Asígnala en texto plano: el mutator del modelo la hashea.
- Roles por defecto: **Becario** y **Paciente**.
- Debe cambiar la contraseña en el primer inicio de sesión. Agrega a la tabla de usuarios la columna `debe_cambiar_password` (boolean) y la fecha del último cambio.
  - Middleware que, mientras esté en true, redirija toda petición autenticada a una pantalla de "Cambiar contraseña" (Tailwind, sin Bootstrap).
  - Permite solo esa pantalla y el logout.
  - Valida que la nueva contraseña no sea igual a la cédula y tenga mínimo 8 caracteres.
  - Registra el cambio en la bitácora.
- Si la persona ya tiene usuario, no crees otro. Si su cuenta ya existe, solo asegúrate de que tenga los roles Becario y Paciente, sin quitarle roles.

**Revisa cómo hace hoy el login la búsqueda del usuario.** Si busca por `username` = correo:
- Haz que acepte cédula **o** correo.
- Los usuarios existentes deben poder seguir entrando con su correo.
- El bloqueo por intentos fallidos debe seguir funcionando con ambos.

**Importación masiva (miles de filas):**
- Procesa en lotes con `chunk` dentro de transacciones y usa un Job en cola si el proyecto tiene colas configuradas. Si no hay colas, procésalo en la misma petición con un límite de tiempo razonable.
- El hash bcrypt es costoso. Para 5.000 filas usa el costo configurado, pero no hagas consultas fila por fila: precarga las cédulas existentes en memoria.
- Al terminar, muestra un resumen: personas creadas, actualizadas, usuarios creados, filas con error y motivo.
- Valida duplicados dentro del mismo archivo y cédulas vacías o mal formadas.
- Registra la importación en la bitácora con el resumen, sin datos personales en `observacion`.
- Crea un comando `php artisan usuarios:generar-faltantes` que cree la cuenta a toda persona existente que no tenga usuario, con las mismas reglas.

# Fase 3 — Multisede

La universidad tiene varias sedes. Cada sede debe operar con sus propios datos: inventario, personas, consultas, comedor, transporte, becas, etc. Hoy `persona.id_sede` existe, pero el sistema no filtra por sede.

- **Sede del usuario:** la sede de un usuario es la de su persona. Guárdala en sesión al iniciar sesión, junto a la lógica de `ModuleFilter`, sin cambiar la redirección.
- **Superadmins:** ven todas las sedes. Agrega un selector de sede en el navbar (Tailwind + Alpine) que fije `session('sede_activa')`, con la opción "Todas las sedes".
- **Filtrado automático:** crea un trait o *global scope* reutilizable, por ejemplo `PerteneceASede`.
  - Filtra las consultas por la sede activa.
  - Asigna la sede automáticamente al crear registros.
  - Aplícalo a cada modelo cuya tabla tenga sede directa.
  - En tablas sin sede directa, filtra por relación: consulta → consultorio → sede, lote → `inventario_sede_lote`, etc.
- **Columnas de sede que faltan:** agrégalas donde el DER del profesor ya las define (`consumo_diario`, `detalle_registro_comedor`, `registro_comedor`, `sobrante_comedor` y `compra` en lugar de `modulo_id`, etc.). Revisa tabla por tabla con `MAPEO_DER.md`.
- **Inventario:** las entradas, salidas, dispensaciones, consumos del comedor y préstamos deben afectar solo el stock de la sede activa (`inventario_sede_lote` + `inventario_total`). No se puede despachar stock de otra sede.
- **Listados y búsquedas:** personas, pacientes, beneficiarios y búsquedas AJAX (buscar persona, buscar paciente) deben quedar limitados a la sede, salvo para superadmins.
- **Reportes:** reportes y estadísticas filtran por la sede activa.
- **Seeders:** que siembren al menos 2 sedes, y personas e inventario en ambas.
- **Pruebas:** un usuario de la sede A no ve ni modifica datos de la sede B, y un superadmin con "Todas" ve ambas.

# Fase 4 — `inventario_total`

Es un consolidado del stock por **producto y sede**, sin importar el lote. Sirve para responder "¿cuánta harina hay en la sede A?" sin sumar lotes en cada consulta.

- Crea la migración siguiendo el DER del profesor: `producto`, `sede`, `cantidad_total`, índice único (producto, sede).
- Crea un servicio único, por ejemplo `App\Support\Inventario\ServicioInventario`, con métodos `entrada`, `salida`, `ajuste`, `transferir` y `recalcular`. Debe actualizar en la **misma transacción** el lote, `inventario_sede_lote`, `inventario_total` y `movimiento_inventario` (con `cantidad_anterior` y `cantidad_final`).
- Refactoriza **todos** los lugares que hoy mueven stock para que usen el servicio: compras finalizadas, lotes, merma de vencidos, dispensación de medicamentos, consumo diario del comedor, préstamos de deporte si existen, etc. Búscalos con grep (`cantidad_actual`, `inventario_sede_lote`, `movimiento_inventario`).
- Usa `lockForUpdate` para evitar condiciones de carrera.
- Crea el comando `php artisan inventario:recalcular-totales`, que reconstruya `inventario_total` desde `inventario_sede_lote`. Ejecútalo en el seeder.
- Muestra el total por producto en las vistas de stock (Existencias por sede, Productos) con alerta de stock mínimo y máximo.
- Pruebas: entrada, salida, salida sin stock suficiente (debe fallar sin dejar datos inconsistentes) y recalcular.

# Fase 5 — Tablas del modelado propio que faltan

Compara `diagrama.dbml` (en `docs/` o en la raíz del proyecto; búscalo) contra las migraciones reales. Lista en el reporte cada tabla definida en el modelado que **no** tenga migración o que no se use en el código.

- Si una tabla es necesaria para lo que ya existe en el sistema (por ejemplo, un módulo que la referencia sin tenerla), impleméntala.
- Si pertenece a un módulo completo que no existe (por ejemplo, deportes), solo documéntala; no construyas módulos nuevos.

# Fase 6 — Adaptar a la nomenclatura del DER del profesor

Es la fase más grande y riesgosa. Hazla **módulo por módulo**, con un commit por módulo y todas las verificaciones en verde antes de pasar al siguiente. Orden:

1. Catálogos base.
2. Geografía y sede.
3. Persona, usuario y seguridad.
4. Inventario.
5. Comedor.
6. Salud.
7. Psicología.
8. Transporte.
9. Becas.
10. Deportes, si existe.

Si un módulo no queda en verde, reviértelo, documéntalo y continúa con el siguiente.

## Cómo adaptar
- **Tablas y columnas:** usa los nombres de `MAPEO_DER.md`, y para las columnas los del DER del profesor: PK `id_xxx`, FK `xxx_id`, columnas `nombre_xxx`.
- **Modelos Eloquent:** conserva los nombres de clase actuales y ajusta `$table`, `$primaryKey`, `$fillable`, `$casts`, relaciones y claves foráneas.
- **Consultas fuera de Eloquent:** busca con grep **todo** uso del nombre viejo de tabla y columna y adáptalo: `DB::table`, `join`, `where` con strings, `DB::raw`, reglas `exists:`/`unique:` de validación, vistas Blade, JS que lea propiedades JSON, exportaciones, seeders, factories, pruebas y la configuración de permisos.
- **Columnas de auditoría:** agrega `status_id` (FK a la nueva tabla `status`, sembrada con Activo e Inactivo), `deleted_at` (SoftDeletes), `created_id` y `updated_id`, llenados automáticamente con un trait u *observer* desde el usuario autenticado.
  - Las columnas booleanas actuales `activo`/`estado`/`status` que significan activo o inactivo pasan a `status_id`. Adapta los toggles "activar" para que usen `status_id`.
- **Tipos de PostgreSQL:** el DER usa `timestamptz` y `jsonb`; en MySQL usa `timestamp` y `json`.
- **Tabla `usuario` → `user`:** cambia la configuración de auth (`config/auth.php`, provider, `getAuthIdentifierName`), `sessions.user_id`, la cola de notificaciones, el broadcasting del chat y el canal privado `App.Models.Usuario.{id}`. Verifica que login, logout, bloqueo, llave maestra, recuperación de contraseña y chat sigan funcionando.
- **Columnas que el profesor no tiene:** conserva las que el sistema necesita, por ejemplo las de propuestas en `citas`, `modulo.key` y `exchange_rates`. Aplícales la nomenclatura del profesor y documenta la justificación.
- **Estructuras que no coinciden con la del profesor:** en `localidad` → `parroquia`, `persona` → `telefono_persona`, `be_lapsos` → `lapso_academico` y `persona_pnf` → `estudiante`, adopta la estructura del profesor si puedes adaptar el código completo. Si no, conserva la actual con la nueva nomenclatura y documéntalo como pendiente para hablar con el profesor.
- **Inconsistencias del propio DER:** no las copies (están listadas al final de `MAPEO_DER.md`). Resuélvelas de la forma razonable y documéntalas. Para `persona.sed_id`, **sí crea la columna**, porque el multisede la necesita.
- **Bitácora:** debe seguir funcionando; ajusta las exclusiones de campos sensibles con los nombres nuevos (`password_use`, `master_key_use`, etc.).
- **Permisos:** `config/menu_routes.php`, `config/menu_permissions.php` y los nombres de rutas **no cambian**, porque no dependen de los nombres de tablas. Verifícalo.
- **Diagrama actualizado:** al terminar, genera `docs/der/diagrama_adaptado.dbml` con el esquema final real, leído desde las migraciones, para mostrarlo al profesor.

# Fase 7 — Reporte final

Crea `docs/der/REPORTE_ADAPTACION.md` con estas secciones:

1. **Resumen ejecutivo:** qué se completó y qué quedó pendiente, por fase.
2. **Tabla final de equivalencias** (actual → profesor), con el estado de cada una: adaptada / conservada con justificación / pendiente.
3. **Cambios que el profesor debe conocer:**
   - Columnas o tablas que existen en el sistema y no en su DER, con la justificación.
   - Inconsistencias encontradas en su DER.
   - La diferencia MySQL/Laravel 10 frente a PostgreSQL/Laravel 11.
4. **Decisiones tomadas sin consultar.**
5. **Cómo probar manualmente cada fase:**
   - Psicólogo A frente a B.
   - Importar un Excel de prueba.
   - Primer login con la cédula.
   - Cambio de sede.
   - Movimiento de inventario.
6. **Lista de commits.**

Crea también un Excel de ejemplo para importar (`docs/der/ejemplo_importacion.xlsx` o `.csv`, según lo que acepte el importador) con 20 filas, incluyendo filas inválidas a propósito.

Al terminar, deja el árbol de trabajo limpio y escribe en la terminal un resumen corto con la ruta del reporte.
