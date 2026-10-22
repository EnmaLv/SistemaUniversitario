# Reporte de mejoras de módulos — Sistema de Bienestar Estudiantil (UPTP)

Rama: `feature/mejoras-modulos` (creada desde `feature/adaptacion-der`). Sin push.
Encargo: `PROMPT_MEJORAS_MODULOS.md`.

Verificación final de la rama: `php -l` de todos los PHP tocados, compilación de las 151 vistas Blade modificadas, `php artisan route:list` (530 rutas), `php artisan migrate:fresh --seed` + `php artisan permisos:sincronizar` sin errores y `php artisan test` con 136 pruebas aprobadas.

> **Antes de empezar:** después del trabajo de adaptación al DER se fusionó la rama `deiby` (módulo de deportes de un compañero). Sus migraciones apuntaban a `sede.id` y `usuario.id_usuario`, que ya no existen, así que `migrate:fresh` fallaba, y su prueba usaba `RefreshDatabase` con el esquema anterior. Se corrigió primero lo mínimo para que el sistema migrara; la reestructuración completa está en la Fase 7.

---

## 1. Resumen ejecutivo por fase

| Fase | Estado | Resultado |
|---|---|---|
| 1. Matriz de permisos sin checks | Completada | Corregida la causa (sección 2) y otros dos restos del mismo tipo. Prueba `MatrizPermisosTest` (falla sin la corrección). |
| 2. Botones según permiso | Completada | `x-table-actions` oculta cada acción según el permiso de la ruta que genera y muestra el candado "Solo lectura" si la fila queda sin acciones. Revisadas las 339 vistas; unas 90 protegidas con `@canRoute`/`@canAction`. Prueba `BotonesPorPermisoTest`. |
| 3. Personas, perfiles y roles | Completada | "Registro de persona"; perfiles Estudiante, Docente, Obrero, Administrativo y Comunidad universitaria; formulario e importador con perfil; Paciente para todos y Becario solo para estudiantes, también al cambiar de perfil; becas solo para estudiantes; ejemplo de importación con perfil. Prueba `PerfilesComunidadTest`. |
| 4. Flujos de inventario | Completada | Auditados compra, comedor (FEFO), dispensación, merma, préstamos, devoluciones, reposiciones y entregas: todos pasan por `ServicioInventario` y actualizan lote, existencia por sede, total y movimiento en la misma transacción. `inventario:recalcular-totales` no cambia nada sobre una base sembrada. Matriz en la sección 4. |
| 5. Salud | Completada | Estadísticas por edad, PNF y tipo de persona; fecha real de la consulta con motivo de registro tardío; catálogo de unidades de dosis y conversión a la unidad de inventario; miligramos; stock de medicamentos en solo lectura para Doctor(a) y Enfermero(a). |
| 6. Filtro por tipo de producto | Completada | Productos, lotes, inventario por sede (y su tabla de totales) y movimientos filtran por tipo, lo recuerdan en la URL y arrancan en el tipo del único módulo del usuario. |
| 7. Deportes | Completada | Reestructurado a las tablas del DER; espacios, actividades, calendarios, horarios, torneos con cartel PDF, inscripciones con cupo, asistencia con cierre y reporte; préstamos con sanciones, vencimiento programado, devolución y reposición; solvencia con constancia PDF; entrega de material de oficina; compras de implementos; rol Coordinador de Deportes. |
| 8. GPS de transporte | Completada | Trazado de referencia por ruta (Douglas-Peucker), descarte de puntos dentro de la tolerancia, guardado solo de desvíos, última posición y acumulados en el viaje, nueva versión de referencia desde el administrador, mapa con referencia y desvíos. API sin cambios de contrato. |
| 9. Reporte | Este documento | |

Ninguna fase quedó revertida.

---

## 2. Causa raíz del bug de la Fase 1

El partial `resources/views/admin/configuracion/partials/matriz-permisos.blade.php` agrupaba los permisos de cada vista con `$vista->permisos->keyBy('accion')`. En la adaptación al DER (módulo 3) la columna pasó a llamarse `accion_pmi`. `keyBy` sobre una clave inexistente no falla: agrupa todo bajo una clave vacía, así que `$porAccion->get('ver')` devolvía `null` y cada celda pintaba "—" (no aplica) en lugar del checkbox. Los datos estaban bien: `vista` y `permiso` se sembraban y `CatalogoPermisos::agrupado()` los devolvía.

Por qué no se detectó antes: la herramienta de renombrado cubría columnas en consultas y accesos a propiedades, no claves de colecciones; la prueba de humo solo detecta errores 500 y la página respondía 200; el modo estricto de atributos de Eloquent no aplica a `keyBy`/`data_get`.

Corrección: `keyBy('accion_pmi')`. Un escaneo de todas las claves de colección (`keyBy`, `pluck`, `groupBy`, `firstWhere`…) contra las columnas reales del esquema encontró otros dos casos del mismo tipo: la barra de navegación mostraba "Usuario" en lugar de los roles (`pluck('nombre')`) y el inicio de psicología del estudiante (`pluck('nombre', 'slug')`). La prueba `MatrizPermisosTest` verifica que crear rol, editar rol (con sus checks marcados) y editar permisos de un usuario rendericen checkboxes, y que guardar un rol persista sus permisos; falla si se revierte la corrección.

---

## 3. Tablas y columnas nuevas (justificación frente al DER del profesor)

| Tabla / columna | Fase | Justificación |
|---|---|---|
| `consulta_medica.motivo_registro_tardio_cme` | 5 | Constancia de por qué la atención se registró en otra fecha. La tabla está en el DER; la columna no. |
| `unidad_dosis` (`id_udo`, `nombre_udo`, `abreviatura_udo`) | 5 | Catálogo de unidades para recetar (tableta, cucharada, gota…), distinto de `unidad_medida` (unidad de inventario). El DER guarda la unidad prescrita como texto (`unidad_prescrita_drm`), que se sigue usando. |
| `unidad_medida`: fila "Miligramo" (`mg`, factor 0,001) | 5 | Datos, no estructura. |
| `trazado_ruta_transporte` (`id_trt`, `version_trt`, `orden_trt`, `lat_trt`, `lng_trt`, `vigente_trt`, `rtr_id`, `vjt_id`) | 8 | Ruta de referencia por versión; el DER no tiene dónde guardarla. |
| `log_gps_transporte.es_desvio_lgt`, `tramo_desvio_lgt` | 8 | Desde que una ruta tiene referencia, solo se guardan desvíos; estas columnas los marcan y agrupan por tramo. Una tabla aparte duplicaría los puntos. |
| `viaje_transporte`: `ultima_lat_vjt`, `ultima_lng_vjt`, `ultima_velocidad_vjt`, `ultima_posicion_en_vjt`, `ultima_recepcion_en_vjt`, `ultimo_local_id_vjt`, `puntos_recibidos_vjt`, `velocidad_max_vjt`, `velocidad_promedio_vjt`, `distancia_urbana_km_vjt`, `distancia_carretera_km_vjt`, `ralenti_horas_vjt`, `en_desvio_vjt`, `tramos_desvio_vjt` | 8 | Como los puntos sobre la ruta ya no se guardan, el seguimiento en vivo y el cálculo de combustible necesitan la última posición y los acumulados en el viaje. |
| `espacio_deportivo`, `actividad_deportiva`, `calendario_deportivo`, `horario_deportivo`, `inscripcion_deportiva`, `torneo_deportivo`, `equipo_torneo_deportivo`, `integrante_equipo_deporte`, `prestamo_deportivo`, `sancion_deportiva` | 7 | Tablas del DER (reemplazan `deporte_espacio`, `deporte_actividad`, `deporte_calendario`, `deporte_horario` del compañero). |
| `espacio_deportivo.sed_id` | 7 | Una cancha está en una sede; sin esto un coordinador vería espacios de otras sedes y la detección de solapes los mezclaría. |
| `horario_deportivo.semana_inicio_hde`, `motivo_cancelacion_hde` | 7 | Columnas que el código del compañero ya usaba, con sufijo. |
| `equipo_torneo_deportivo.prg_id` nullable → `programa.id_prg` | 7 | El DER apunta a `pnf.id_pnf`, tabla inexistente (inconsistencia ya documentada). Nullable porque hay equipos libres o mixtos. |
| `sesion_asistencia_deportiva` (`id_sad`, `hde_id`, `fecha_sad`, `cerrada_sad`, `cerrada_en_sad`, `cerrada_por_use_id`) y `asistencia_deportiva` (`id_asd`, `sad_id`, `ide_id`, `estado_asd`) | 7 | El DER solo tiene `estado_ide` (una asistencia por inscripción) y un horario semanal tiene muchas sesiones. |
| `prestamo_deportivo`: `cantidad_prd`, `motivo_prd`, `epd_id`, `lugar_prd`, `estado_devolucion_prd`, `sed_id`; estados `repuesto` y `sancionado` | 7 | Para qué y dónde se presta, en qué estado se devolvió, qué sede pierde y recupera la existencia. |
| `sancion_deportiva`: `forma_levantamiento_snd`, `lot_id`; `aplicada_por_use_id` nullable | 7 | Cómo se levantó (devolución o reposición) y a qué lote entró la reposición. **Para hablar con el profesor:** el DER exige `aplicada_por_use_id`, pero las sanciones por vencimiento las aplica el sistema. |
| `entrega_material` (`id_ema`, `fecha_ema`, `per_id`, `dependencia_ema`, `motivo_ema`, `sed_id`, `use_id`) y `detalle_entrega_material` (`id_dem`, `ema_id`, `pdt_id`, `cantidad_dem`) | 7 | Entrega de consumibles de oficina; no existe en el DER. |
| `perfil`: filas Docente, Obrero, Administrativo, Comunidad universitaria | 3 | Datos, no estructura. |
| `tipo_producto`: Implemento deportivo, Material de oficina | 6 | Datos, no estructura. |

Todas las tablas nuevas siguen la nomenclatura del profesor (`id_xxx`, sufijos de 3 letras, FK `xxx_id`) y llevan `status_id`, `deleted_at`, `created_id`, `updated_id` y timestamps.

---

## 4. Matriz de flujos de inventario

Todos los flujos usan `App\Support\Inventario\ServicioInventario`, que en una transacción con `lockForUpdate` actualiza `lote.cantidad_actual_lot`, `inventario_sede_lote`, `inventario_total` y crea el `movimiento_inventario` con cantidad anterior y final, sede, usuario y referencia (`referencia_type_moi` + `referencia_id_moi`).

| Flujo | Tipo de movimiento | Referencia | Prueba |
|---|---|---|---|
| Compra finalizada | ENTRADA (solo en la sede de la compra) | `compra` | `InventarioFlujosTest::test_compra_finalizada_entra_a_la_sede_de_la_compra`, `InventarioTest` |
| Servir comida | SALIDA por ingrediente y porciones, FEFO (vence antes, sale antes) | `consumo_diario` | `InventarioFlujosTest::test_servir_comida_descuenta_ingredientes_por_fefo` |
| Dispensación de medicamentos | SALIDA en unidad de inventario (convertida desde la dosis) | `Dispensacion` | `DispensacionDosisTest` (tabletas, mg, no convertible), `FlujoSaludTest` |
| Merma de vencidos | MERMA (ajuste negativo a cero en la sede activa; lote inactivo si queda sin existencia) | `lote_vencido` | `InventarioFlujosTest::test_merma_de_vencidos_deja_el_lote_en_cero` |
| Préstamo de implemento | SALIDA | `prestamo_deportivo` | `DeportesPrestamosTest::test_inventario_cuadra_despues_de_prestar_y_devolver` |
| Devolución en buen estado | ENTRADA al mismo lote y sede | `prestamo_deportivo` | `DeportesPrestamosTest::test_inventario_cuadra_despues_de_prestar_y_devolver`, `test_devolucion_en_buen_estado_levanta_la_sancion` |
| Reposición de implemento | ENTRADA (lote nuevo u original) | `sancion_deportiva` | `DeportesPrestamosTest::test_reposicion_levanta_la_sancion_y_entra_al_inventario` |
| Entrega de material de oficina | SALIDA por lotes | `entrega_material` | `DeportesPrestamosTest::test_entrega_de_material_descuenta_inventario_y_rechaza_sin_existencia` |
| Recálculo de totales | — | — | `InventarioFlujosTest::test_recalcular_totales_en_base_sembrada_no_cambia_nada` |

Cada prueba verifica que la suma de `inventario_sede_lote` del producto en la sede sea igual a `inventario_total` (en unidades de presentación y en unidad base) y que `lote.cantidad_actual_lot` sea la suma de sus existencias.

---

## 5. Decisiones tomadas sin consultar

**Fase 1 y 2**
- `x-table-actions` resuelve la ruta de cada acción a partir de la URL y el método (`App\Support\Permisos\PermisoUrl`) y consulta `puedeRuta`; si la ruta no existe, el botón se oculta (antes llevaba a un 404/405).
- En las pantallas de crear/editar no se envuelve el botón de guardar cuando la propia pantalla ya exige esa acción ("protegidas por pantalla"); los modales se protegen con la ruta de su formulario; los botones generados en JavaScript reciben booleanos calculados en el servidor.
- Arreglos de paso: `/register` respondía siempre 403 (consultaba `rol.nombre`/`rol.slug`); la ruta de activar categorías tenía el mismo nombre que la de productos (`admin.maestros.productos.activar` → `admin.maestros.categorias.activar`); el formulario de eliminar nota de evolución apuntaba a `/citas/{id}`; dos peticiones de la agenda usaban URLs relativas.

**Fase 3**
- Perfiles: Usuario (1, cuentas internas), Estudiante (2), Docente, Obrero, Administrativo, Comunidad universitaria. El listado de personas excluye los perfiles internos (Usuario, Administrador).
- Importador: columna `perfil`/`tipo`/`tipo de persona`; una persona nueva sin perfil queda como Estudiante; una persona existente sin perfil en el archivo conserva el que tenía (para que un archivo viejo no convierta docentes en estudiantes); un perfil desconocido va a errores.
- Al dejar de ser estudiante no se borra el PNF ni el semestre anteriores; solo se dejan de pedir.
- El cambio de rol Becario por perfil se registra en la bitácora (`rol_becario_por_perfil`) e invalida la caché de permisos.
- Los cuatro médicos y enfermeros del `PersonaSeeder` tenían perfil Estudiante; pasan a Administrativo.
- Se corrigió de paso: la edición de personas siempre fallaba (`Persona::actualizarPersona` filtraba por una columna inexistente).

**Fase 4**
- La merma de vencidos usa el tipo `MERMA` (un ajuste negativo) en lugar de `AJUSTE`, para que el historial distinga la causa.

**Fase 5**
- La edad se calcula a la fecha de la consulta, en PHP (portable). Se quitaron estadísticas con rangos y valores fijos que no coincidían con la base y los filtros de tipo de persona y PNF se cargan desde la base.
- `cantidad_prescrita_drm` y `cantidad_drm` quedan en unidad de dosis; `cantidad_dsm` y `unm_id` de la dispensación, en unidad de inventario. Unidades contables (tableta, cápsula, ampolla, sobre, supositorio, unidad) convierten 1:1 si el producto se maneja por unidad; mg/g/ml/l por factor; cucharada, cucharadita, gota, aplicación e inhalación no tienen conversión y obligan a indicar la cantidad a descontar.
- Doctor(a) y Enfermero(a) ven el stock en la pantalla de medicamentos (con estado del stock) con solo `ver`. `PermisosSeeder` ahora acepta acciones por vista y agrega los permisos faltantes a roles que ya tenían otros.

**Fase 6**
- Material de oficina se asocia al módulo `administracion` (el que mejor encaja); las compras de deportes lo ofrecen también.
- No hay una pantalla propia de "inventario total": ese papel lo cumple la tabla "Total por producto" del inventario por sede (y los totales de su índice).
- Valor inicial del filtro: superadministrador → Todos; un solo módulo con un solo tipo asociado → ese tipo; varios → Todos. Los módulos sin tipo de producto no cuentan.

**Fase 7**
- **Implementos de oficina:** se interpretan como consumibles que se **entregan** a una persona (por cédula) o a una dependencia (texto); no se prestan ni se devuelven. La entrega descuenta del inventario por lotes y se rechaza completa si no hay existencia.
- Un implemento devuelto **dañado** genera sanción igual que uno perdido (no vuelve al inventario) hasta su reposición.
- Una persona no puede pedir otro implemento si tiene un préstamo activo, vencido o sancionado, o una sanción activa, en cualquier sede y con cualquier perfil.
- `deportes:marcar-vencidos` corre cada hora (los préstamos son por horas) y la misma verificación se hace al consultar préstamos, sanciones, solvencia y al prestar.
- La solvencia se calcula con verificadores por módulo (`App\Services\Solvencia\SolvenciaService`); hoy solo existe el de deportes.
- Horarios semanales: se repiten cada semana desde `semana_inicio_hde` hasta el cierre del calendario (antes solo valían la semana en que se crearon). Los solapes se detectan por espacio, día, horas y rango de fechas.
- Inscripciones: el sistema no tenía autoinscripción, así que inscribe el coordinador buscando por cédula. Torneo = horario: las inscripciones al torneo usan el cupo del horario.
- Compras de deportes: los productos sin vencimiento (implementos, material) no exigen fecha de vencimiento al finalizar la compra.
- La clave del módulo es `deporte` (la sembrada); el sidebar y el inicio usaban `deportes` y nunca se mostraban.

**Fase 8**
- **Tolerancia del GPS: 50 metros** (`config/transporte.php`, variable `TRANSPORTE_TOLERANCIA_DESVIO`): cubre el error del GPS de un teléfono (5–20 m), el ancho de la vía y el error de la simplificación. Simplificación Douglas-Peucker con 10 m.
- El primer viaje finalizado de una ruta crea la referencia; mientras una ruta no tiene referencia se guardan todos los puntos como antes.
- Duplicados: un punto es duplicado si su `local_id` ya está en el log, si es el último recibido o si cae dentro de la tolerancia sin ser más reciente que la última posición. La respuesta de la API no cambia.
- La app del conductor también escribe su posición directo en Firestore (`buses_activos`); esas posiciones no pasan por el backend. El único punto de entrada del backend es `POST /api/viajes/{viaje}/gps`.
- `config/Transporte.php` (con mayúscula) nunca se leía en Linux; se renombró a `config/transporte.php`.
- Los puntos GPS se excluyen de la auditoría automática de modelos (cada punto generaba una fila en la bitácora).
- No hay viajes sembrados; para datos reales: `php artisan transporte:generar-trazados`.

---

## 6. Cómo probar manualmente

Preparación:

```bash
docker exec SisgestiondeBienestraEstudiantil php artisan migrate:fresh --seed
docker exec SisgestiondeBienestraEstudiantil php artisan permisos:sincronizar
```

Administrador: `admin@example.com` / `12345678`, llave maestra `masterkey123`.

### Fase 1 — Matriz de permisos
1. Configuración → Roles → Crear rol: la matriz muestra un checkbox por vista y acción. Marca algunos y guarda.
2. Edita el rol: los permisos guardados aparecen marcados.
3. Configuración → Permisos → editar un usuario: se ven los permisos heredados del rol (verde) y se pueden agregar o bloquear.

### Fase 2 — Botones según permiso
1. Crea un rol con solo "Ver" en Proveedores y asígnalo a un usuario de prueba.
2. Entra con ese usuario a Proveedores: no hay botón "Nuevo" y cada fila muestra el candado "Solo lectura".
3. Dale todas las acciones: aparecen Nuevo, Editar e Inactivar.

### Fase 3 — Personas y perfiles
1. Administración → Personas → Registrar persona. Elige perfil Docente: PNF y semestre desaparecen. Guarda.
2. Importa `docs/mejoras/ejemplo_importacion.csv` en Archivos: el resumen muestra las filas con perfil inválido. Al terminar la cola, un docente tiene solo el rol Paciente y un estudiante tiene Paciente y Becario (Configuración → Empleados/Permisos).
3. Cambia el perfil de un estudiante a Obrero: pierde Becario y conserva sus otros roles.
4. Entra como el docente a Becas → Solicitar: el sistema indica que solo los estudiantes pueden solicitar.

### Fase 4 — Inventario
1. Compras: crea, agrega productos y finaliza; revisa Inventario por sede y Historial de movimientos (entrada en la sede de la compra).
2. Comedor → Registro de comida: sirve raciones; se descuentan los lotes que vencen antes.
3. Lotes → Mermar vencidos: el lote vencido queda en cero.
4. `docker exec SisgestiondeBienestraEstudiantil php artisan inventario:recalcular-totales`: los totales no cambian.

### Fase 5 — Salud
1. Salud → Consultas → Nueva con fecha de ayer: exige el motivo de registro tardío; con motivo se guarda y el detalle y el PDF muestran ambas fechas. La bitácora registra `consulta_registro_tardio`.
2. Recetación: el select ofrece unidades de dosis (tableta, cucharada…) y precarga la presentación del medicamento. Dispensa: las tabletas se convierten solas; una cucharada pide la cantidad a descontar.
3. Entra con un usuario Doctor(a): Medicamentos muestra el stock de su sede sin botones de crear ni editar.
4. Inicio de salud: gráficos por edad, PNF y tipo de persona con los filtros de fecha y sede.

### Fase 6 — Filtro por tipo
1. Productos, Lotes, Inventario por sede e Historial: elige "Medicamento" en el filtro; la URL guarda `?tipo=`. Cambia de página y exporta el PDF: el filtro se conserva.
2. Entra con un usuario que solo tenga Salud: el filtro arranca en Medicamento.

### Fase 7 — Deportes
1. Asigna el rol Coordinador de Deportes a un usuario y entra al módulo Deporte.
2. Crea un espacio, una actividad, un calendario y un horario semanal; intenta otro horario en el mismo espacio y hora: se rechaza por solape.
3. Crea un torneo sobre un horario y descarga su cartel PDF. Inscribe personas por cédula hasta el cupo: la siguiente se rechaza.
4. Asistencia: abre la sesión del día, marca algunos presentes y cierra; los demás quedan inasistentes. Revisa el reporte.
5. Préstamos: presta un balón a una cédula; intenta un segundo préstamo: se rechaza. Pon la devolución esperada en el pasado y ejecuta `php artisan deportes:marcar-vencidos`: aparece la sanción y la persona no puede pedir más. Devuelve o registra la reposición: la sanción se levanta y el inventario se actualiza.
6. Solvencia: consulta la cédula sancionada (no solvente, no se puede firmar) y descarga la constancia PDF.
7. Entregas de material: entrega papelería a una dependencia; el inventario baja.
8. Compras: crea una compra del módulo Deporte con balones y finalízala; entran al inventario de la sede.

### Fase 8 — GPS
1. Programa un viaje en una ruta sin referencia y, con la app del conductor (o `POST /api/viajes/{id}/gps`), envía puntos; finaliza: se crea la referencia (versión 1).
2. Repite otro viaje por la misma ruta: el log casi no crece, pero la posición en vivo se actualiza.
3. Envía puntos alejados más de 50 m: se guardan como desvío y el viaje queda con "hubo desvío"; el mapa del viaje los dibuja en naranja sobre la referencia azul.
4. En el detalle de un viaje finalizado, "Marcar como ruta de referencia" crea la versión 2.

---

## 7. Lista de commits

| Commit | Mensaje |
|---|---|
| `6f085e3` | Agrega el encargo de mejoras de módulos |
| `1bf5253` | Corrige la matriz de permisos sin checks (fase 1) |
| `9c74acd` | Oculta los botones de acción según los permisos (fase 2) |
| `6e26ae8` | Registra a toda la comunidad universitaria con perfiles (fase 3) |
| `9752dea` | Verifica los flujos de inventario (fase 4) |
| `2717b94` | Mejora el módulo de salud (fase 5) |
| `5ae02b8` | Filtra el inventario por tipo de producto (fase 6) |
| `9bc8c17` | Reestructura y completa el módulo de deportes (fase 7) |
| `c7b699b` | Guarda la ruta de referencia del GPS y solo los desvíos (fase 8) |
| (este commit) | Agrega el reporte de mejoras de módulos (fase 9) |

Rama `feature/mejoras-modulos` sobre `feature/adaptacion-der` (que ya incluía la fusión de la rama `deiby`). La eliminación de la prueba obsoleta `tests/Feature/DeporteHorarioGlobalOccupancyTest.php` quedó en el primer commit (el del encargo) porque ya estaba preparada en el índice. Varios archivos tocados por más de una fase (por ejemplo vistas con botones protegidos en la fase 2 y textos o campos nuevos en otra) quedaron en el commit de la última fase que los modificó.

Verificación final: `php artisan test` → 136 pruebas aprobadas; `php artisan migrate:fresh --seed` y `php artisan permisos:sincronizar` sin errores; `php artisan route:list` → 530 rutas.

---

## Anexo — Vistas revisadas en la Fase 2

Se revisaron las 339 vistas de `resources/views`. Resumen por módulo de lo protegido (ruta usada entre paréntesis cuando aplica):

- **Becas:** beneficios, criterios, preguntas, jornada (crear, avanzar lapso), solicitudes (crear, aprobar/rechazar/revocar con `verificar`).
- **Configuración:** bitácora (exportar CSV/PDF por botón), bloqueos (desbloquear), empleados (editar, eliminar), personas (crear, editar), roles (crear, eliminar).
- **Maestros de comedor y administración:** categorías, PNF (crear, editar, inactivar, activar), productos (crear, nueva categoría), proveedores, sedes, recetas (pasa a usar `x-table-actions`), ingredientes de recetas.
- **Movimientos:** compras (exportar, crear, nuevo proveedor, cancelar, finalizar), historial (PDF), lotes (mermar), registro diario.
- **Ubicaciones y enfermedades:** estados, municipios, localidades (crear/editar/inactivar/reactivar en los componentes Livewire), enfermedades.
- **Livewire:** compras (agregar/eliminar ítem, enviar correo, fechas), registro diario (registrar, Excel, PDF, finalizar día), registro de comida, registro de persona, archivos (procesar, descargar).
- **Psicología:** agenda (PDF, acciones generadas en JS: realizar, no asistió, posponer, cancelar, aceptar, rechazar, proponer, quitar propuesta), citas, historias (exportaciones y edición del expediente), avances, campos de evolución, estados de ánimo, plantillas, grupos, horarios, prioridades, publicaciones.
- **Salud:** categorías, consultorios, envases, medicamentos, consultas, horarios.
- **Transporte:** cargas de combustible, mantenimientos, marcas, modelos, combustibles, paradas, rutas, vehículos, viajes.
- **Deportes:** horarios, y todas las pantallas nuevas de la Fase 7.
- **Componentes:** tasa BCV, formulario de producto, menús de exportar de los paneles de inicio, inicio del estudiante, detalle de cita, aviso de atención.
- **Sin botones de escritura:** autenticación, chat, errores, inicio, landing, correos, PDF, paginaciones y vistas de solo lectura (show).

Detectado sin corregir (no son botones de esta fase): vistas huérfanas que apuntan a rutas inexistentes (`citas/alerta-prioridad`, `plantillas_globales/create`, `grupos_horarios/show`, `auth/security_questions`, `auth/verify`); botón PDF del listado de compras sin manejador; dos rutas con URI `admin/admin/` (`compras.cancelar`, `comedor.estadisticas`); las acciones de los componentes Livewire no pasan por `CheckMenuPermission` (la interfaz las oculta, pero el servidor no las valida); las exportaciones de los paneles de inicio usan la ruta de estadísticas con `?format=` y resuelven a la acción `ver`.
