Vas a trabajar solo durante la noche, sin nadie que responda preguntas. Antes de empezar, lee completos `CLAUDE.md`, `MAPEO_DER.md`, `docs/der/REPORTE_ADAPTACION.md` y `docs/seguridad/REPORTE_SEGURIDAD.md` para entender el estado actual del sistema recordando los cambios que hiciste hasta ahora.

# Reglas de trabajo autónomo

- **No te detengas a preguntar.** Ante una ambigüedad, toma la decisión más razonable, aplícala y anótala en "Decisiones tomadas" del reporte final.
- Crea la rama `feature/mejoras-modulos` desde la rama actual. Haz un commit por fase. No hagas push.
- Usa siempre `docker exec` para artisan, composer y phpunit (ver `CLAUDE.md`).
- Respeta todo lo ya construido:
  - Nomenclatura del DER del profesor.
  - Permisos granulares por vista y acción (`@canRoute`, `@canAction`, `CheckMenuPermission`).
  - Bitácora.
  - Multisede.
  - Servicio único de inventario.
  - Confidencialidad de psicología.
  - Compatibilidad con PostgreSQL.
  - Sin Bootstrap.
  - Sin comentarios que no sean funcionales.
- Toda vista o ruta nueva:
  - Se registra en `config/menu_permissions.php` y `config/menu_routes.php`.
  - Se sincroniza con `php artisan permisos:sincronizar`.
  - Se enlaza en el sidebar con `@canMenu`.
  - Sus botones se protegen con `@canRoute`.
- Después de cada fase, todas estas verificaciones deben pasar antes de hacer commit:
  - `php -l` de los archivos tocados.
  - `php artisan route:list`.
  - `php artisan migrate:fresh --seed`.
  - `php artisan test`.
- **Si una fase no queda en verde, reviértela**, documenta el motivo y continúa con la siguiente.
- Puedes modificar migraciones de creación existentes (la base de datos es de desarrollo) y crear tablas nuevas. Cada tabla nueva sigue la nomenclatura del profesor (`id_xxx`, sufijos de 3 letras, `status_id`, `deleted_at`, `created_id`, `updated_id`) y se justifica en el reporte.

# Fase 1 — Formulario de roles y permisos sin checks (urgente)

**Síntoma:** al crear o editar un rol, o al editar los permisos especiales de un usuario, la matriz de permisos no muestra ningún check. Probablemente se rompió durante la adaptación al DER del profesor.

**Pasos:**
1. Reprodúcelo y encuentra la causa. Revisa como mínimo:
   - `CatalogoPermisos::agrupado()`.
   - Las relaciones de los modelos `Vista`, `Permiso` y `Modulo` (nombres de tabla, PK, FK y columnas renombradas, por ejemplo `activo` → `status_id`).
   - `SincronizadorPermisos`.
   - Que `PermisosSeeder` corra en `DatabaseSeeder` después de módulos y roles.
   - Que las tablas `vista` y `permiso` tengan datos después de `migrate:fresh --seed`.
   - El partial `admin/configuracion/partials/matriz-permisos.blade.php`.
   - Los controladores `RolesController` y `PermisosController`.
2. Corrígelo.
3. Escribe pruebas:
   - Después de `migrate:fresh --seed`, existen vistas y permisos.
   - La pantalla de crear rol, la de editar rol y la de editar permisos de un usuario renderizan al menos un checkbox de permiso.
   - Guardar un rol con permisos los persiste.

# Fase 2 — Ocultar todos los botones según permiso

Recorre **todas** las vistas Blade del sistema: index, show, create, edit, modales, `x-table-actions` y exportaciones.

- Cada botón o enlace de crear, editar, eliminar, activar/desactivar y exportar (PDF, Excel, Word, ZIP) debe mostrarse solo si el usuario tiene el permiso, usando `@canRoute('nombre.ruta')`.
- Modifica el componente `resources/views/components/table-actions.blade.php` para que oculte cada acción (ver, editar, toggle, eliminar) según `puedeRuta` de la ruta que genera. Así quedan cubiertas todas las tablas a la vez.
- Si una fila se queda sin ninguna acción visible, muestra el candado de "Solo lectura" que ya existe en recetas.
- Documenta en el reporte la lista de vistas revisadas.
- Prueba: un rol con solo `ver` en una vista no ve ningún botón de acción en ella. Con todas las acciones, los ve todos.

# Fase 3 — Personas, perfiles y roles por defecto

- **Renombrado en la interfaz:** cambia "Registro de estudiante" por **"Registro de persona"** en menús, títulos, botones y mensajes.
- **Comunidad universitaria completa:** el registro (manual y por Excel) acepta estudiantes, docentes, obreros, administrativos y comunidad universitaria en general. El tipo de persona lo define la tabla `perfil` (estudiante, docente, obrero, etc.).
  - Revisa que `perfil` tenga sembrados al menos: Estudiante, Docente, Obrero, Administrativo, Comunidad universitaria.
  - El formulario y el importador deben permitir elegir o leer el perfil.
  - Los campos propios de estudiante (PNF, semestre) se piden solo si el perfil es Estudiante.
- **Roles automáticos al crear la cuenta:**
  - **Todas** las personas reciben el rol **Paciente**.
  - **Solo** las de perfil Estudiante reciben además el rol **Becario**, porque solo a ellos se les asignan becas.
  - Corrige el servicio de creación automática de cuentas y el comando `usuarios:generar-faltantes`.
  - Si una persona cambia de perfil, ajusta el rol Becario (agregar o quitar) sin tocar sus demás roles.
- **Becas:** el módulo de becas solo debe permitir solicitudes de personas con perfil Estudiante.
- **Excel de ejemplo:** actualízalo en `docs/` con la columna de perfil.

# Fase 4 — Verificación del inventario y sus movimientos

Audita y corrige cada flujo que mueve stock. Todos deben pasar por el servicio único de inventario. Cada uno debe actualizar en la misma transacción el lote, `inventario_sede_lote`, `inventario_total` y `movimiento_inventario` (con cantidad anterior y final, sede, usuario y referencia polimórfica).

**Flujos a revisar:**
1. **Compra finalizada o recibida** → entrada. Crea los lotes, con vencimiento si aplica, en la sede de la compra.
2. **Servir comida** (consumo diario, registro de comida o registro diario, según cómo funcione hoy) → salida de cada ingrediente de la receta según las porciones. Respeta FEFO: primero los lotes que vencen antes.
3. **Dispensación de medicamentos en la consulta** → salida en la unidad correcta, con conversión de unidades.
4. **Merma de vencidos** → ajuste.
5. **Préstamos y devoluciones de deportes** (Fase 7) → salida y entrada.
6. **Entrega de implementos de oficina** (Fase 7) → salida.

**Para cada flujo:**
- Escribe una prueba Feature que verifique los cuatro lugares que se actualizan y que el saldo cuadre: suma de `inventario_sede_lote` igual a `inventario_total`.
- Corrige cualquier flujo que hoy descuente directo sin el servicio, que use la sede equivocada o que no registre el movimiento.
- Ejecuta `inventario:recalcular-totales` en una base sembrada y verifica que no cambie nada. Si cambia, hay un flujo inconsistente.

# Fase 5 — Salud

1. **Métricas o estadísticas de salud:** agrega distribución por **edad** (rangos), **PNF** y **tipo de persona** (perfil: estudiante, docente, obrero, comunidad universitaria), con los filtros de fecha y sede existentes.
2. **Fecha real de la consulta:**
   - La consulta guarda `created_at` (cuándo se registró en el sistema) y la fecha real de atención, que ya existe (`fecha_cme` o equivalente) y debe ser editable en el formulario.
   - Si la fecha real es distinta del día de registro, es obligatorio llenar un campo nuevo **motivo del registro tardío**.
   - Valida que la fecha real no sea futura.
   - Muestra ambas fechas en el detalle y en el PDF.
   - Registra el motivo en la bitácora.
3. **Unidades de prescripción:**
   - En crear medicamento ya se puede indicar la presentación o dispensación (cucharada, tableta, gotas, etc.). Pero en la consulta, al recetar, el select muestra las unidades de medida de inventario (ml, l, g, kg), y un médico receta en dosis: "1 cucharada", "1 pastilla cada 8 horas".
   - Corrige el select de la receta para ofrecer las **unidades de dosis**. Usa un catálogo (tabla nueva si hace falta) con tableta/pastilla, cápsula, cucharada, cucharadita, gota, ml, mg, ampolla, sobre, aplicación, etc. Precarga la presentación de dispensación del medicamento elegido.
   - Agrega **miligramos (mg)** a las unidades de medida, con su factor de conversión respecto a gramo.
   - La dispensación convierte la dosis prescrita a la unidad de inventario usando los factores del producto (unidades por presentación, contenido). Si no hay conversión posible, pide la cantidad a descontar explícitamente.
   - Revisa que el PDF del récipe muestre la dosis tal como la escribió el médico.
4. **Stock visible para médicos y enfermeros:**
   - Los roles Doctor(a) y Enfermero(a) deben poder **ver** cuánto queda de cada medicamento en su sede, en **solo lectura**: sin crear, editar ni comprar.
   - Elige la mejor forma: la vista de inventario total filtrada a medicamentos o la de medicamentos con su stock. Dales solo el permiso `ver` en el seeder.

# Fase 6 — Filtro por tipo de producto en las vistas de inventario

Las vistas de **lotes**, **inventario por sede y lote**, **inventario total**, **productos** y **movimientos** deben filtrar por tipo de producto: **Alimento**, **Medicamento**, **Implemento deportivo**, **Material de oficina**, más los demás tipos que existan.

- Usa `tipo_producto` como filtro y siémbralo con esos valores.
- Recuerda el filtro elegido en la URL.
- Si un usuario solo tiene acceso a un módulo, el filtro arranca en el tipo de ese módulo: un médico ve Medicamento y el comedor ve Alimento.

# Fase 7 — Módulo de Deportes

El código de deportes lo hizo un compañero y es el módulo menos maduro. Revísalo completo y corrige la lógica que esté mal. Puedes reestructurarlo si es necesario, siempre con las tablas del DER del profesor: `espacio_deportivo`, `actividad_deportiva`, `calendario_deportivo`, `horario_deportivo`, `inscripcion_deportiva`, `torneo_deportivo`, `equipo_torneo_deportivo`, `integrante_equipo_deporte`, `prestamo_deportivo`, `sancion_deportiva`. Crea las tablas nuevas que hagan falta.

Funciona por sede, como el resto del sistema, y tiene su propio módulo (`deporte`) en `modulos`, sidebar y permisos. Crea el rol **Coordinador de Deportes** con todos sus permisos.

## 7.1 Préstamo de implementos deportivos
- Se presta a **cualquier persona de la universidad**: estudiante, docente, obrero, etc. No se limita por perfil.
- Se busca a la persona por **cédula**. Si no existe en la base de datos, no se puede prestar.
- **Una persona puede tener un solo préstamo activo a la vez:** un balón de fútbol, uno de baloncesto o un equipo de ping pong.
- Datos del préstamo:
  - Persona, implemento (producto y lote).
  - Fecha y hora de entrega.
  - **Para qué** se presta (motivo o actividad) y **dónde** estará (espacio deportivo o texto).
  - **Desde y hasta cuándo**: inicio y devolución esperada.
  - **Cuándo se devolvió** y en qué estado (bueno, dañado, perdido).
  - Usuario que entregó y usuario que recibió.
- El préstamo es una salida del inventario de la sede; la devolución en buen estado es una entrada. Usa el servicio de inventario.
- Préstamo **vencido** (pasó la fecha esperada sin devolver):
  - Pasa a estado vencido. Hazlo con un comando programado (`deportes:marcar-vencidos`, registrado en el scheduler) y también al consultar.
  - Genera una **sanción** activa.
- **Con sanción activa la persona no puede pedir más implementos.** La sanción se levanta cuando:
  - Devuelve el implemento, o
  - Lo **repone** comprando uno igual. Se registra la reposición como entrada al inventario con referencia a la sanción.
  - Guarda quién la levantó y cuándo.
- Aplica igual a docentes y a cualquier otro perfil: sin devolver no pueden pedir más.

## 7.2 Solvencia institucional
- Pantalla "Solvencia" (permiso propio) que, con una cédula, muestre si la persona está **solvente** o tiene sanciones o deudas activas en deportes (préstamo no devuelto ni repuesto). Debe quedar preparada para agregar otros módulos después.
- Si tiene sanción activa, la pantalla indica claramente que **no se puede firmar la solvencia** para el título, con el detalle de la sanción.
- Exportable a PDF con la constancia de solvencia o no solvencia.
- Registra cada consulta en la bitácora.

## 7.3 Espacios, actividades, horarios y torneos
- Revisa y corrige lo que hizo el compañero en espacios, actividades, calendario y horarios.
- **Torneos:** cada torneo se asocia a un espacio, con día y hora.
  - Genera un **PDF imprimible tipo cartel** con el nombre del torneo, el espacio, el día, la hora y cómo inscribirse, para pegarlo en la universidad.
  - Haz lo mismo para las actividades programadas.
- **Inscripciones:**
  - Cualquier persona de la universidad puede inscribirse en torneos y actividades, buscándola por cédula, respetando el cupo máximo.
  - Si el sistema ya permite que el usuario se inscriba él mismo desde su cuenta, mantenlo. Si no, haz que el coordinador lo inscriba.
- **Asistencia:**
  - Por cada horario o fecha de actividad o torneo, el coordinador pasa asistencia sobre los inscritos.
  - Los que no se marquen como presentes quedan como **inasistentes** al cerrar la asistencia.
  - Agrega un reporte de asistencia.

## 7.4 Implementos de oficina
- Bienestar también gestiona material de oficina (lápices, cuadernos, hojas, etc.).
- Agrégalo como tipo de producto **Material de oficina**, con sus categorías, dentro del inventario existente.
- Interpretación por defecto: son **consumibles que se entregan** a una persona o dependencia. No se prestan ni se devuelven. Crea una vista de **entrega de material** que:
  - Registre a quién se entrega (por cédula o dependencia), qué, cuánto y para qué.
  - Descuente del inventario.
  Documenta esta decisión.

## 7.5 Compras de deportes
- Los implementos deportivos y de oficina se piden desde el apartado de **Compras** existente: balones, raquetas, etc.
- Al recibirse, entran al inventario de la sede con el servicio de inventario.
- Verifica que Compras permita elegir productos de esos tipos y que el Coordinador de Deportes pueda crear compras de su módulo.

## 7.6 Pruebas mínimas
- Préstamo a cédula inexistente → rechazado.
- Segundo préstamo con uno activo → rechazado.
- Préstamo vencido → sanción y bloqueo de nuevos préstamos.
- Devolución → levanta la sanción.
- Reposición → levanta la sanción y entra al inventario.
- Solvencia de una persona con sanción → no solvente.
- Inscripción respetando el cupo.
- Cierre de asistencia → los no marcados quedan inasistentes.
- El inventario cuadra después de prestar y devolver.

# Fase 8 — GPS de transporte: ruta de referencia y desvíos

Hoy `log_gps_transporte` guarda todos los puntos de todos los viajes, aunque la ruta casi nunca cambia.

**Nueva lógica:**
1. **Primer viaje finalizado de una ruta:** su trazado se guarda como **ruta de referencia**. Crea una tabla nueva con la nomenclatura del profesor, por ejemplo `trazado_ruta_transporte`: puntos ordenados lat/lng por ruta y versión, con la referencia al viaje de origen. Simplifica el trazado (Douglas-Peucker o muestreo por distancia) para no guardar puntos redundantes.
2. **Viajes siguientes:**
   - Cada punto recibido se compara contra el trazado de referencia: distancia del punto al segmento más cercano.
   - Si está dentro de la tolerancia configurable (`config/transporte.php`, por defecto 50 metros), **no se guarda** el punto. Solo se actualiza la última posición conocida del viaje, para el seguimiento en vivo, y los acumulados del viaje: distancia, velocidad máxima o promedio.
   - Si se sale de la tolerancia, se guardan **solo los puntos del desvío**, se marca `hubo_desvio_vjt = true` y se registra el tramo del desvío. Si existe el campo de motivo, el conductor o el admin pueden llenarlo después.
3. **Cambio de ruta:** un administrador puede marcar el trazado de un viaje como nueva referencia (nueva versión). Ejemplo: la ruta cambió de forma permanente.
4. **Compatibilidad:** revisa de dónde llegan los puntos (app del conductor o Firebase, `firebase_id_vjt`, endpoint de API) y adapta ese punto de entrada. No rompas el formato que envía la app. Si no encuentras el emisor, adapta el endpoint existente y documéntalo.
5. **Datos existentes:** migra los logs para generar la referencia de cada ruta desde su primer viaje completo.
6. **Mapa:** la vista del viaje debe dibujar la ruta de referencia y, encima, los tramos de desvío en otro color.
7. **Pruebas:** el primer viaje crea la referencia, un viaje igual no guarda puntos, un viaje con desvío guarda solo el desvío y marca `hubo_desvio`.

# Fase 9 — Reporte final

Crea `docs/mejoras/REPORTE_MEJORAS.md` con estas secciones:
1. **Resumen ejecutivo por fase:** qué se completó y qué no.
2. **Causa raíz del bug de la Fase 1.**
3. **Tablas y columnas nuevas,** con la justificación frente al DER del profesor.
4. **Matriz de flujos de inventario:** flujo → tipo de movimiento → prueba que lo cubre.
5. **Decisiones tomadas sin consultar.** Incluye la interpretación de los implementos de oficina y la tolerancia del GPS.
6. **Cómo probar manualmente cada fase, paso a paso.**
7. **Lista de commits.**

Al terminar, deja el árbol de trabajo limpio y escribe en la terminal un resumen corto con la ruta del reporte.
