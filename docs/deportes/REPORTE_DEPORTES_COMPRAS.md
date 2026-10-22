# Reporte de deportes y compras — Sistema de Bienestar Estudiantil (UPTP)

Rama: `feature/deportes-compras` (creada desde `feature/mejoras-modulos`). Sin push.
Encargo: `PROMPT_DEPORTES_COMPRAS.md`.

Verificación:
- **Fase 1, por separado:** se verificó en una copia aislada del repositorio, sin los cambios de deportes. Pasaron `php -l` de todos los PHP tocados, la compilación de todas las vistas Blade, `route:list` (544 rutas), la migración completa con todos los seeders sobre la base de pruebas y `php artisan test` (141 pruebas aprobadas).
- **Fases 1 y 2 juntas:** pasaron `php -l`, la compilación de todas las vistas, `route:list` (541 rutas: la Fase 2 eliminó rutas de horarios que ya no aplican), `migrate:fresh --seed` + `permisos:sincronizar` y `php artisan test` (149 pruebas aprobadas).
- **Conversión de datos:** la migración de datos de deportes se probó con `php artisan migrate` sobre la base de desarrollo, que todavía tenía los horarios con la estructura anterior (sección 2.3).

---

## 1. Resumen por fase

| Fase | Estado | Resultado |
|---|---|---|
| 1.1 Compras solo en Administración | Completada | Compras se quitó de los sidebars de comedor y deportes, y se eliminó la vista de permisos `comedor_compras`. El Coordinador de Deportes perdió `compras`, `productos` y `proveedores`; ningún rol del seeder tiene compras salvo los superadmins (Administrador y Secretaria de Bienestar). Los demás módulos conservan la consulta de existencias. |
| 1.2 Sede principal y reparto | Completada | Se agregó la columna `sede.es_principal_sed`, sembrada en Acarigua; el código no escribe ese nombre. Solo la sede principal crea, modifica y recibe compras, y el servidor lo valida en el controlador y en los componentes Livewire. Al recibir la compra, cada línea se reparte entre las sedes activas: `floor(cantidad / sedes)` a cada una y el sobrante a la principal, con dos decimales si la unidad es de peso o volumen y en enteros si es contable. Todo ocurre en una sola transacción mediante `ServicioInventario::entrada` y se protege contra la doble recepción. La pantalla muestra la vista previa del reparto antes de confirmar y el reparto aplicado en el detalle. |
| 1.3 Formulario por tipo de producto | Completada | El formulario se adapta al tipo: alimentos por peso o volumen (acepta decimales), medicamentos por envase ("cada envase trae N tabletas", con el total en unidad mínima), implementos y material de oficina por unidad o caja. El precio vigente sale del registro más reciente de `precio_producto`, convertido a Bs con la última tasa, y se muestra por kg, por envase o por unidad. Se corrigió la causa del precio vacío en alimentos (sección 1.1). Hay pantallas nuevas de **Implementos deportivos** (sidebar de deportes) y **Material de oficina** (Administración → Configuración General). |
| 2 Deportes | Completada | Actividades esporádicas por defecto y recurrentes por excepción; jornadas con bloques simultáneos y sección opcional; choques de espacio validados entre todas las actividades; torneos con inscripción por equipos (sin "Inscritos"); inscripción individual por cédula con cupo opcional. Asistencia sobre un evento concreto, también de días anteriores: marcado por cédula o pegando listas, no inscritos con aviso, cierre con inasistentes, reapertura registrada en la bitácora, reporte por evento y reporte por persona solo en recurrentes. Inicio de deportes, agenda semanal por espacio, carteles PDF nuevos, conversión de los datos anteriores y seeders de ejemplo. |
| 3 Reporte | Este documento | |

Ninguna fase quedó revertida.

### 1.1 Causa del precio vacío en el formulario de compra

`ItemsCompra::updatedProductoId` leía `producto.precio_compra_pdt` en lugar de `precio_producto`. Esa columna guarda un valor en Bs que solo se llenaba en dos casos:
- cuando `procesarTasaYPrecios` corría con una tasa registrada, al crear o editar en Productos o Medicamentos;
- en `MedicamentoSeeder`, que la fijaba en 800.

`ProductoSeeder` nunca la llenaba y `crearProducto` la guarda en 0. Por eso los alimentos llegaban con precio 0 o vacío aunque `PrecioProductoSeeder` sí tenía sus precios. Los medicamentos "funcionaban" por ese 800 fijo, sin tener ningún registro en `precio_producto`.

Además, `procesarTasaYPrecios` lanzaba "No existe una tasa registrada" y deshacía la creación del producto cuando no había tasa.

Corrección:
- El formulario usa `App\Support\Inventario\PrecioVigente`: último registro de `precio_producto`, con el costo en USD (es una compra, no una venta), convertido a Bs con la última `tasa_cambio`.
- Si no hay tasa, el campo queda vacío y el formulario avisa, mostrando el costo en USD como referencia.
- `MedicamentoSeeder` ahora siembra `precio_producto`.
- `procesarTasaYPrecios` ya no lanza la excepción.
- Las compras ya no usan `precio_compra_pdt`.

---

## 2. Cambios de base de datos y su justificación frente al DER del profesor

### 2.1 Fase 1

| Tabla | Cambio | Justificación |
|---|---|---|
| `sede` (DER) | Nueva columna `es_principal_sed` (boolean, por defecto `false`), agregada en la migración de creación de `sede`. Acarigua se marca en `SedeSeeder`. | La regla "solo la sede principal compra y recibe el sobrante del reparto" necesita saber cuál es esa sede sin escribir su nombre en el código. Deducirla por id o por nombre sería frágil, y una tabla de configuración aparte sería excesiva para un solo dato. Respeta la nomenclatura (sufijo `_sed`). **Para hablar con el profesor.** |

Datos de seeders corregidos:
- `ProductoSeeder`: los alimentos sembrados en "Gramo" con contenido 1000 pasan a "Kilo", y Leche, Agua y Refresco pasan de "Kilo" a "Litro". Así el formulario dice "Cantidad (kg)" o "Cantidad (l)" y el precio es por kg o por litro.
- `MedicamentoSeeder`: siembra el precio de cada medicamento en `precio_producto`.

### 2.2 Fase 2

Cómo se usa cada tabla del DER (sin cambiar sus nombres):

| Tabla del DER | Significado en el sistema |
|---|---|
| `actividad_deportiva` | **Disciplina** (Fútbol sala, Voleibol…). El DER la describe así. |
| `calendario_deportivo` | **Actividad, jornada o torneo**: el contenedor con nombre, fechas, sede y coordinador. El ejemplo del DER es "Torneo Inter-PNF 2026". |
| `horario_deportivo` | **Bloque**: disciplina + espacio + hora + cupo. Una jornada tiene varios bloques simultáneos. |
| `inscripcion_deportiva` | Inscripción individual en un bloque (`hde_id`), como en el DER. |
| `torneo_deportivo` | Torneo, con su bloque base (`hde_id`), como en el DER. |
| `equipo_torneo_deportivo` / `integrante_equipo_deporte` | Equipos y sus integrantes por cédula. |

Un **evento o sesión** es un bloque en una fecha concreta (`sesion_asistencia_deportiva`). Todos los cambios están en `database/migrations/2026_10_22_000100_reestructura_actividades_deportivas.php`:

| Tabla | Cambio | Justificación |
|---|---|---|
| `calendario_deportivo` (DER) | Columnas nuevas: `tipo_cld` (`esporadica`, `recurrente`, `torneo`; por defecto `esporadica`), `dias_semana_cld` (días separados por coma), `seccion_cld` (texto libre), `con_inscripcion_cld` (boolean) y `prg_id` (FK opcional a `programa`). Índice nuevo `calendario_deportivo_tipo_fechas_idx`. | El DER no distingue un evento único de uno recurrente o de un torneo, ni guarda la sección. `horario_deportivo.dia_semana_hde` admite un solo día: repetir una actividad varios días obligaría a crear un horario por día, y la inscripción y el cupo quedarían repartidos entre ellos. No hay tabla de secciones, y el DER apunta a `pnf.id_pnf`, que no existe en el sistema; por eso el PNF se toma de `programa` y la sección es texto libre. **Para hablar con el profesor.** |
| `horario_deportivo` (DER) | Se eliminó `semana_inicio_hde`, que no es del DER (la agregó la fase 7 de mejoras). Las columnas del DER no cambian; `dia_semana_hde` queda en null porque los días ahora viven en la actividad y `fecha_especifica_hde` se llena en los eventos de una sola vez. | Simplifica el modelo. **Para hablar con el profesor:** con este diseño `dia_semana_hde` deja de usarse. |
| `asistencia_deportiva` (propia, no del DER) | Se reconstruyó con `per_id` (FK a `persona`), `ide_id` opcional, `ied_id` nuevo (FK a `integrante_equipo_deporte`) y una sola marca por persona y sesión (único `sad_id` + `per_id`). | Hay asistentes no inscritos e integrantes de equipo que no tienen inscripción individual, así que la asistencia ya no puede colgar solo de `inscripcion_deportiva`. |

Las demás tablas de deportes no cambian.

### 2.3 Conversión de los datos existentes

La migración convierte los datos que haya; en una base vacía no hace nada.
1. **Asistencias:** se copian con el `per_id` tomado de su inscripción.
2. **Torneos:**
   - Cada torneo recibe su propio `calendario_deportivo` de tipo torneo, y su horario pasa a ese calendario.
   - Si dos torneos compartían horario, el segundo recibe una copia del bloque.
   - Las personas inscritas directamente en el torneo pasan al equipo **"Participantes sin equipo"**. Su inscripción queda retirada y borrada lógicamente, y su asistencia se enlaza al integrante.
3. **Horarios semanales:**
   - Se agrupan por disciplina + espacio + horas + cupo + estado y forman una actividad **recurrente** con todos sus días.
   - Los horarios duplicados de otros días se unifican en un bloque; las inscripciones y sesiones se mueven sin duplicarse.
   - Un grupo semanal con una sola fecha se convierte en **evento de una sola vez**. Si no tiene ninguna fecha, también queda cancelado.
4. **Horarios con fecha específica:** forman un evento de una sola vez por fecha.
5. **Limpieza:** los calendarios que quedan vacíos se borran lógicamente.

Resultado sobre la base de desarrollo: los 20 horarios anteriores (19 semanales) quedaron en 12 bloques activos: 10 actividades recurrentes, 1 esporádica y 1 torneo. Ninguna asistencia quedó sin persona. Se creó el equipo "Participantes sin equipo" para los inscritos directos del torneo, y de las 4 inscripciones anteriores quedó vigente 1. `down()` revierte la estructura, no los datos.

---

## 3. Manual de uso: actividades, torneos y asistencia

### Compras (Administración)
- Las compras se hacen desde **Administración → Compras y Requisiciones**, y solo desde la sede principal.
- Al agregar un producto, el sistema trae su último precio y pregunta la cantidad en la unidad correcta:
  - kilos o litros para los alimentos;
  - envases para los medicamentos (indica cuántas tabletas trae cada uno);
  - unidades o cajas para los implementos y el material de oficina.
- Cuando llega la mercancía, en el paso **Recepción** verás cuánto recibirá cada sede. Al pulsar **Recibir y repartir**, cada sede recibe su parte en su inventario y la sede principal se queda con lo que sobre de la división.
- Las otras sedes no compran: reciben su parte automáticamente.

### Actividades y jornadas
- Una actividad es algo que pasa en los espacios deportivos. Al crearla eliges si ocurre **una sola vez** (una jornada o una práctica en una fecha) o si **se repite cada semana** (el entrenamiento del equipo de fútbol). En las que se repiten, marcas los días y el rango de fechas desde y hasta.
- Luego agregas uno o más **bloques**: qué deporte, en qué cancha y a qué hora. Si una sección hace una jornada con fútbol, baloncesto y voleibol al mismo tiempo, agrega un bloque por cada deporte en una cancha distinta. Puedes indicar el PNF y la sección.
- El sistema no te deja ocupar una cancha que ya está tomada ese día a esa hora, aunque sea por otra actividad.
- Si marcas **"Requiere inscripción previa"**, la gente se anota con su cédula en **Inscripciones**, eligiendo el bloque. El cupo es opcional. Si no lo marcas, la actividad es abierta y solo se toma la asistencia.
- Desde la actividad puedes descargar el **cartel en PDF** con todos sus bloques.
- En **Horarios de los espacios** ves la semana de cada cancha con los bloques ocupados. En el teléfono se muestra como lista.

### Torneos
- Un torneo tiene deporte, cancha, fechas y hora.
- En los torneos nadie se inscribe solo. Entra en la pantalla del torneo, crea cada **equipo** (con su PNF si representa a uno) y agrega sus **integrantes por cédula**. Puedes pegar varias cédulas separadas por coma.
- Una persona solo puede estar en un equipo de cada torneo.
- El cartel del torneo explica cómo inscribir un equipo.

### Asistencia
1. Entra en **Asistencia** y elige el evento en la lista de hoy y de los últimos días. Si fue antes, elige la fecha. Puedes registrarla el mismo día o días después.
2. Escribe la cédula de cada persona presente y pulsa **Enter**: verás su nombre y el campo queda listo para la siguiente. Si tienes la lista en papel, pega todas las cédulas juntas, una por línea o separadas por coma.
3. Si alguien asistió sin estar inscrito, se marca igual y aparece en amarillo. Si una cédula no existe, el sistema te lo dice. Arriba ves en vivo **"presentes X de Y inscritos"**.
4. Cuando termines, pulsa **Cerrar asistencia**: los inscritos o integrantes que no marcaste quedan como inasistentes.
5. Si hubo un error, quien tenga permiso de edición puede **reabrirla**, indicando el motivo. Eso queda registrado.

Cada evento tiene su **reporte** con presentes, inasistentes y personas que asistieron sin inscripción. En las actividades que se repiten cada semana, **"Asistencia por persona"** muestra cuántas sesiones asistió cada uno de las que hubo.

### Inicio de deportes
Al entrar al módulo verás:
- los eventos de hoy y los próximos;
- los préstamos activos y vencidos;
- las sanciones activas;
- las existencias de implementos, con aviso **"Reponer"** cuando bajan del mínimo;
- botones para prestar, devolver, tomar asistencia y crear una actividad.

---

## 4. Decisiones tomadas

### Compras
- **Cómo se compra cada tipo:**
  - Alimento con unidad de peso o volumen: se compra en esa unidad, y al inventario entra cantidad × `factor_conversion_unm` (g o ml), igual que lo consume el comedor.
  - Alimento con unidad "Unidad" (paquetes de 500 g, 300 g…): se compra por paquete.
  - Medicamento: siempre por envase, usando `unidades_presentacion_pdt` como factor.
  - Implemento deportivo y material de oficina: por unidad, o por caja si el producto tiene envase o más de una unidad por presentación.
- **Fraccionable o contable:** lo decide el tipo y la unidad del producto al momento de repartir.
  - Las partes se calculan en centésimas enteras para no perder decimales.
  - La principal recibe la cantidad menos la suma de las demás, tanto en presentación como en unidad convertida.
  - Las partes en 0 no generan movimiento.
- **Quién puede comprar:**
  - Un superadmin con la sede principal activa, o con "Todas", puede comprar.
  - Un superadmin con otra sede activa recibe el aviso de que debe cambiarla.
  - Un usuario de otra sede recibe el mensaje de que su sede recibe su parte automáticamente.
  - Si la sede principal no existe o está inactiva, nadie puede recibir, y la vista previa lo explica.
- La compra se guarda siempre con `sed_id` de la sede principal y solo se modifica mientras está Pendiente.
- **Módulos de las compras:** el módulo "Administración" se puede elegir para comprar material de oficina. Las compras del módulo Deporte ofrecen implementos y material de oficina.
- **Ubicación de Material de oficina:** queda en `admin.maestros.material_oficina.*` porque lo gestiona Administración. Implementos deportivos queda en `admin.deportes.implementos.*`.
- **Precio:** se usa el costo (`costo_usd_ppr`) y no el precio con margen, porque se trata de una compra.

### Deportes
- **Nombres de rutas:** se conservaron para no mover claves de permisos. `admin.deportes.actividades.*` son ahora las **Disciplinas** y `admin.deportes.calendarios.*` son las **Actividades y jornadas**; el menú y las pantallas usan los nombres nuevos.
- **Asistencia por bloque y fecha:** en una jornada con bloques simultáneos, cada bloque tiene su propia asistencia.
- **Quiénes se esperan en cada sesión:**
  - Actividad con inscripción: los inscritos vigentes del bloque.
  - Torneo: todos los integrantes de todos los equipos.
  - Actividad abierta: nadie, así que no hay aviso de "no inscrito" ni conteo "de Y".
- **Fechas de asistencia:**
  - No se toma asistencia de fechas futuras; las pasadas no tienen límite.
  - La lista muestra hoy y los últimos 14 días, con buscador y la opción "¿Fue en otra fecha?".
  - Se pueden crear actividades con fecha pasada para cargar la asistencia tarde.
- **Duración:** un evento de una sola vez dura un día; si dura varios días, se registra como recurrente.
- **Torneos:**
  - Tienen un rango de fechas y días de juego opcionales; sin días marcados, se juega todos los días del rango.
  - La asistencia es por día de juego, no por partido.
  - Al cancelarlo se libera el espacio; al finalizarlo todavía se puede cargar asistencia tarde.
- **Permisos de asistencia:**
  - Marcar, quitar un presente por error y cerrar requieren `crear`.
  - Reabrir requiere `editar` (registrado explícitamente en `config/permisos.php`). Queda en la bitácora con los datos anteriores y el motivo, borra las inasistencias generadas y devuelve las inscripciones a "inscrito".
- **Cédulas:** se aceptan con V-/E-, puntos y espacios. Las listas pegadas se separan por salto de línea, coma, punto y coma o tabulador, y se ignoran las repetidas.
- **Personas inactivas:** se pueden marcar presentes, porque es un hecho que asistieron, pero no se pueden inscribir.
- **Edición de actividades:** no se puede quitar un bloque con inscritos o asistencia, ni bajar el cupo por debajo de los inscritos actuales.
- **Equipos e integrantes:**
  - No se puede eliminar un equipo cuyos integrantes tienen asistencia.
  - Quitar un integrante con asistencia es un borrado lógico, y si se vuelve a agregar se restaura.
- **Disciplinas:** `tipo_acd` (columna del DER) sigue en la tabla, pero ya no se muestra en pantalla.
- **Búsquedas:** usan `LOWER(...) LIKE`, porque en PostgreSQL `LIKE` distingue mayúsculas.
- **Seeders de ejemplo** (en la primera sede activa):
  - jornada esporádica "Jornada deportiva de Informática 631" con 3 bloques simultáneos;
  - entrenamiento recurrente de lunes y miércoles con 3 sesiones pasadas cerradas;
  - torneo "Copa Aniversario de Baloncesto" con 2 equipos de 5 integrantes;
  - balones de fútbol sala y voleibol con existencia, un préstamo activo y uno vencido con su sanción.

### Pendientes conocidos
- No hay pantalla para elegir la sede principal; hoy se fija en el seeder o en la base de datos.
- Si un implemento se registra por caja, los préstamos y entregas descuentan por presentación (`salida`), es decir, una caja entera. Los implementos que se prestan deben registrarse por unidad; otra opción es cambiar esos servicios a `salidaPorConvertida`.
- El listado de Productos muestra la existencia en la unidad base (g) junto a la unidad de compra (kg). Este problema ya existía antes de esta rama.
- `precio_compra_pdt` queda como columna heredada sin uso en compras.

---

## 5. Cómo probar cada punto manualmente

Preparación:

```bash
docker exec SisgestiondeBienestraEstudiantil php artisan migrate:fresh --seed
docker exec SisgestiondeBienestraEstudiantil php artisan permisos:sincronizar
```

Administrador: `admin@example.com` / `12345678`, llave maestra `masterkey123`.

### Fase 1 — Compras
1. **Menú:** entra al módulo Comedor y al de Deportes: no aparece Compras. En Administración sí aparece "Compras y Requisiciones". Con un Coordinador de Deportes, `admin/movimientos/compras` responde 403.
2. **Sede principal:**
   - Con la sede activa en Guanare, "Nueva compra" aparece deshabilitado y explica que solo compra la sede principal.
   - Cambia a Acarigua (o "Todas") y crea la compra.
3. **Formulario por tipo:**
   - Agrega un alimento (por ejemplo Harina): la etiqueta dice "Cantidad (kg)", acepta 7,5 y el precio dice "por kg".
   - Agrega un medicamento: dice "cada envase trae N tabletas" y el resumen muestra, por ejemplo, "5 × 10 = 50 tabletas".
   - Agrega un implemento: cantidad entera "por unidad".
   - En todos los casos el precio se llena solo con el último precio registrado.
4. **Vista previa y reparto:**
   - En el paso "Recepción" revisa la tabla de reparto por sede. Por ejemplo, 10 unidades entre 4 sedes dan 2, 2 y 2 a las otras sedes y 4 a Acarigua.
   - Pulsa "Recibir y repartir".
   - Abre Existencias por Sede: cada sede tiene su parte. En el historial de movimientos hay una entrada por sede con referencia a la compra.
   - Intenta recibir la misma compra otra vez: el sistema lo impide.
5. **Implementos deportivos y Material de oficina:** crea un producto en cada pantalla (Deportes → Implementos deportivos; Administración → Configuración General → Material de oficina) y comprueba que aparece al agregar productos a una compra.

### Fase 2 — Deportes
1. **Inicio:** entra al módulo Deportes y revisa los eventos de hoy y los próximos, los préstamos, las sanciones, las existencias de implementos y los accesos rápidos.
2. **Jornada con bloques:**
   - En "Actividades y jornadas" crea un evento de una sola vez con tres bloques en canchas distintas a la misma hora: se guarda.
   - Intenta otra actividad en una de esas canchas, a una hora que se cruce, en la misma fecha: se rechaza con un mensaje claro.
3. **Recurrente:** crea un entrenamiento de lunes y miércoles con rango de fechas y "Requiere inscripción previa". Inscribe personas por cédula en "Inscripciones" y prueba el cupo.
4. **Torneo:**
   - Abre "Copa Aniversario de Baloncesto": no hay apartado de inscritos.
   - Crea un equipo y agrega integrantes pegando varias cédulas.
   - Intenta agregar a la misma persona en otro equipo: se rechaza.
5. **Asistencia:**
   - En "Asistencia" elige un evento de ayer (o usa "¿Fue en otra fecha?").
   - Escribe una cédula y pulsa Enter.
   - Pega cinco cédulas, una inexistente: el sistema indica cuál no se encontró y marca en amarillo a quien no estaba inscrito.
   - Cierra la asistencia y revisa el reporte: presentes, inasistentes y no inscritos.
   - Reábrela indicando un motivo y busca el registro en la Bitácora.
6. **Asistencia por persona:** en el entrenamiento recurrente sembrado, abre "Asistencia por persona" y comprueba cuántas sesiones asistió cada uno de las 3. En un evento de una sola vez esa opción no aparece.
7. **Horarios de los espacios:** revisa la agenda semanal por cancha y, en una pantalla estrecha, la lista.
8. **Carteles:** descarga el cartel de la jornada (lista los 3 bloques) y el del torneo (fecha, cancha y cómo inscribir un equipo).
9. **Préstamos y sanciones:** presta un balón, devuélvelo y revisa la sanción del préstamo vencido sembrado.

### Pruebas automáticas
- `tests/Feature/Seguridad/ComprasRepartoTest.php`:
  - 10 unidades entre 3 sedes da 3, 3 y 4, con el sobrante en la principal.
  - 7,5 kg entre 2 sedes.
  - El inventario cuadra.
  - Una compra no se recibe dos veces.
  - Una sede no principal no compra.
  - Precio y unidad correctos para cada tipo de producto.
- `tests/Feature/Seguridad/InventarioFlujosTest.php`: la compra finalizada se reparte entre las sedes activas.
- `tests/Feature/Seguridad/DeportesAsistenciaTest.php`:
  - asistencia de un evento de ayer;
  - pegar 5 cédulas con una inexistente;
  - cerrar y verificar inasistentes;
  - reabrir con registro en la bitácora;
  - recurrente con 3 sesiones y su reporte por persona.
- `tests/Feature/Seguridad/DeportesTorneosTest.php`:
  - choques de espacio entre actividades y dentro de una jornada;
  - inscripción individual con cupo;
  - integrantes de torneo por cédula, sin inscripción directa al torneo;
  - carteles PDF.
- `tests/Feature/Seguridad/DeportesPrestamosTest.php`: préstamos, sanciones e inicio de deportes.

---

## 6. Lista de commits

| Commit | Mensaje |
|---|---|
| `5a73e29` | Agrega el encargo de deportes y compras |
| `c3fdb34` | Restringe las compras a Administración y reparte la mercancía entre sedes (fase 1) |
| `885434b` | Reestructura actividades, torneos y asistencia de deportes (fase 2) |
| (este commit) | Agrega el reporte de deportes y compras (fase 3) |
