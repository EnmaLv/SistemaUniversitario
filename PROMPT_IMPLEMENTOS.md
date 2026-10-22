Lee primero `CLAUDE.md` y `docs/deportes/REPORTE_DEPORTES_COMPRAS.md`. Trabaja de forma autónoma: ante una duda, decide según las reglas de este documento, aplícalo y anótalo en "Decisiones tomadas" del reporte.

# Reglas generales

- Crea la rama `feature/implementos` desde la rama actual. Haz un commit por fase. No hagas push.
- Usa siempre `docker exec` para artisan, composer y phpunit.
- Respeta lo ya construido:
  - Nomenclatura del DER del profesor.
  - Permisos con `@canRoute`.
  - Bitácora.
  - Multisede.
  - Servicio de inventario.
  - Compras solo en Administración, con reparto equitativo desde la sede principal.
  - Compatibilidad con PostgreSQL.
  - Tailwind sin Bootstrap.
  - Sin comentarios no funcionales.
  - Textos claros para usuarios con poca experiencia en computadoras.
- Después de cada fase deben pasar `php -l` de los archivos tocados, `php artisan route:list`, `php artisan migrate:fresh --seed` y `php artisan test`. Si una fase no queda en verde, reviértela y documenta el motivo.

# Fase 1 — Medicamentos que aparecen como implementos deportivos (bug)

En la vista "Implementos deportivos" aparecen productos que son medicamentos: ATAMEL 500 MG, BRUGESIC 400 MG, IBUPROFENO 800 MG, con categorías de deportes (Balones, Raquetas y mallas) y presentación "blíster".

- Encuentra la causa. Revisa:
  - El seeder de productos, categorías y tipos.
  - Que la vista filtre por **tipo de producto** y no solo por categoría.
  - La relación `categoria_producto` → `tipo_producto`.
- Corrígela. Cada listado (medicamentos, productos de comedor y la vista de la Fase 3) debe mostrar solo productos de sus tipos.
- Corrige los datos sembrados: cada medicamento debe estar en una categoría del tipo Medicamento.
- Valida en el backend que, al crear o editar un producto desde una vista, la categoría elegida pertenezca a los tipos permitidos de esa vista.
- Prueba: la vista de implementos no muestra medicamentos, y la de medicamentos no muestra implementos.

# Fase 2 — Unidades y presentaciones de los implementos

**Regla de negocio:** en el área de deportes y material, **todo se cuenta por Unidad**. Aunque lleguen 10 balones juntos o una caja de 12 lápices, en el inventario son unidades sueltas.

- **Unidad de medida:** en el formulario de esta área, la unidad de medida siempre es **Unidad**. Muéstrala fija, sin select, y fuérzala en el backend.
- **Compra por caja o paquete:** se mantiene la opción "Se compra por caja o paquete" con "Unidades por caja". La compra se registra en cajas y entra al inventario como unidades: 3 cajas × 12 = 36 unidades.
- **Tabla de presentaciones:** hoy el "Tipo de caja o paquete" usa `envase_primario`, que solo tiene presentaciones de medicinas (blíster, ampolla…). Revisa las migraciones y el uso actual de esa tabla en todo el sistema y elige la mejor solución. Documenta la elección y su justificación frente al DER del profesor:
  - **Opción A:** agregar a `envase_primario` una referencia al tipo de producto (o una tabla puente) para que cada vista muestre solo sus presentaciones. Medicamentos: blíster, frasco, ampolla. Implementos: caja, paquete, bolsa, docena.
  - **Opción B:** otra estructura, si encaja mejor con lo que encuentres.
- **Seeder de presentaciones** del área: Caja, Paquete, Bolsa, Docena, Resma.
- **Medicamentos:** la vista de medicamentos no debe mostrar las presentaciones de implementos, ni la de implementos las de medicamentos.

# Fase 3 — Una sola vista para todo el inventario del área

- Renombra la vista "Implementos deportivos" a un nombre que abarque todo el inventario del área: implementos deportivos **y** material de oficina (lápices, marcadores, cuadernos, resmas, etc.). Sugerencia: **"Implementos y materiales"**. Usa ese nombre en menú, títulos, breadcrumbs, permisos (`config/menu_permissions.php`, `config/menu_routes.php`) y textos.
- La vista permite registrar productos de ambos tipos: **Implemento deportivo** y **Material de oficina**.
  - Al crear, primero se elige el tipo.
  - Las categorías se filtran según el tipo: Balones, Raquetas y mallas, Equipos… para deportes; Escritura, Papelería, Marcadores… para oficina. Siémbralas.
- El listado tiene un filtro por tipo (Todos / Deportivo / Oficina) además del de categoría, y muestra la existencia de la sede activa.
- Ambos tipos quedan disponibles en Compras.
- Si existía una vista separada de material de oficina, unifícala en esta y redirige las rutas viejas.
- Ejecuta `permisos:sincronizar` y conserva los permisos que ya tenían los roles sobre la vista renombrada.

# Fase 4 — El material de oficina también se presta

**Regla de negocio:** el material de oficina funciona como préstamo, igual que los implementos deportivos. Ejemplo: un profesor pide marcadores; se registra a quién se prestan y cuándo se devolvieron.

- Unifica "Préstamos" y "Entregas de material" en un **solo flujo de préstamo** que sirva para cualquier producto del área, deportivo u oficina.
  - Si "Entregas de material" queda vacía de sentido, elimínala o redirígela al préstamo, y documéntalo.
  - Migra las entregas existentes a préstamos.
- **Datos del préstamo:**
  - Persona (por cédula; debe existir).
  - Producto y cantidad.
  - Para qué y dónde.
  - Desde y hasta cuándo.
  - Fecha de devolución y estado al devolver: bueno, dañado, perdido.
  - Usuario que entrega y usuario que recibe.
- **Material que se gasta:** algunos materiales se gastan durante el préstamo, como un marcador. Al devolver, permite indicar que se consumió o se devolvió parcialmente:
  - Se registra la cantidad devuelta.
  - Lo no devuelto queda como salida definitiva del inventario, sin sanción, si se marca como consumo justificado.
  - Lo perdido o no devuelto en plazo sí genera sanción, con las mismas reglas que deportes.
- **Límite de préstamos activos:** revisa la regla de "un préstamo activo por persona", porque un profesor podría necesitar un balón y marcadores a la vez. Aplica el límite **por tipo**: un préstamo activo de implemento deportivo y uno de material de oficina como máximo por persona. Documéntalo.
- **Sanciones y solvencia:** las sanciones por material de oficina bloquean nuevos préstamos y se reflejan en la pantalla de Solvencia, igual que las de deportes.
- **Inventario:** la salida al prestar y la entrada al devolver pasan por el servicio de inventario.
- **Pruebas:**
  - Préstamo de marcadores a un docente.
  - Devolución parcial con consumo.
  - Un deportivo y uno de oficina a la vez permitido; dos deportivos rechazado.
  - Sanción por vencimiento.
  - El inventario cuadra.

# Fase 5 — Reporte

Agrega a `docs/deportes/REPORTE_DEPORTES_COMPRAS.md` una sección "Implementos y materiales" con:
- La causa del bug de la Fase 1.
- La decisión sobre presentaciones (Fase 2) y su justificación.
- Los cambios de base de datos.
- El flujo de préstamo explicado para el usuario final.
- Las decisiones tomadas.
- Cómo probar cada punto manualmente.
- La lista de commits.

Al terminar, deja el árbol de trabajo limpio y escribe en la terminal un resumen corto.
