Lee primero `CLAUDE.md`, `docs/mejoras/REPORTE_MEJORAS.md` y los reportes anteriores en `docs/`. Trabaja de forma autónoma: ante una duda, decide según las reglas de negocio de este documento, aplícalo y anótalo en "Decisiones tomadas" del reporte final.

# Reglas generales

- Crea la rama `feature/deportes-compras` desde la rama actual. Haz un commit por fase. No hagas push.
- Usa siempre `docker exec` para artisan, composer y phpunit.
- Respeta lo ya construido:
  - Nomenclatura del DER del profesor.
  - Permisos granulares y botones protegidos con `@canRoute`.
  - Bitácora.
  - Multisede.
  - Servicio único de inventario.
  - Compatibilidad con PostgreSQL.
  - Tailwind sin Bootstrap.
  - Sin comentarios no funcionales.
- Puedes modificar migraciones existentes (la base de datos es de desarrollo) o crear tablas o columnas nuevas. Justifica cada cambio de estructura en el reporte frente al DER del profesor.
- **Usabilidad:** el sistema lo usará personal administrativo con poca experiencia en computadoras.
  - Cada pantalla debe explicar en una línea qué hace y qué se espera.
  - Usa textos y mensajes de error en lenguaje común, sin términos técnicos.
  - Cuando no haya datos, el estado vacío debe decir qué hacer a continuación.
  - Evita flujos con pasos ocultos.
- Después de cada fase deben pasar `php -l` de los archivos tocados, `php artisan route:list`, `php artisan migrate:fresh --seed` y `php artisan test`. Si una fase no queda en verde, reviértela, documenta el motivo y continúa con la siguiente.

# Fase 1 — Compras

## 1.1 Compras solo en Administración
- La vista de Compras debe aparecer **solo** en el menú del módulo **Administración**. Quítala del sidebar de comedor, salud y deportes, y de cualquier otro.
- Por defecto, solo el **Administrador** y los roles que gestionen el módulo de Administración tienen los permisos de compras. Quita los permisos de compras de los demás roles en los seeders: Coordinador de Deportes, Administrador de Salud, roles de comedor, etc.
- Los demás módulos pueden **ver** el stock, pero no comprar.

## 1.2 Solo la sede principal compra, y la mercancía se reparte equitativamente
- **Solo la sede Acarigua (sede principal) puede crear compras.**
  - Agrega una marca de sede principal a la tabla `sede`, por ejemplo una columna booleana `es_principal_sed`. Siémbrala en Acarigua y justifícala.
  - No dejes el nombre "Acarigua" escrito en el código.
- **Al recibir o finalizar una compra**, cada línea se **reparte equitativamente** entre todas las sedes activas:
  - Cada sede recibe `floor(cantidad / número de sedes)`.
  - El **sobrante** de la división queda en la sede principal.
  - Si la unidad es fraccionable (kg, litros), reparte con decimales y redondea el sobrante hacia la sede principal. Si es contable (unidades, envases, balones), reparte enteros.
- Cada sede recibe su parte como entrada a su inventario (`inventario_sede_lote` + `inventario_total` + `movimiento_inventario`), mediante el servicio de inventario y en una sola transacción.
- Antes de confirmar, la pantalla de la compra muestra una **vista previa del reparto** por sede.
- Revisa el historial con git (`git log -S` sobre el código de compras) para ver cómo funcionaba el reparto anterior. Reutiliza lo que sirva.
- Pruebas:
  - 10 unidades entre 3 sedes → 3, 3 y 4 (la principal recibe el sobrante).
  - 7,5 kg entre 2 sedes.
  - El inventario total cuadra.
  - Una sede no principal no puede crear compras.

## 1.3 Formulario de compra adaptado al tipo de producto
- El formulario cambia sus etiquetas y campos según el **tipo de producto** elegido:
  - **Alimento:** se compra **por peso o volumen** (kg, g, l). No pide "envase". Etiqueta "Cantidad (kg)", según la unidad del producto. No pide unidades por presentación.
  - **Medicamento:** se compra por **envase o presentación**. Pide cantidad de envases y muestra "cada envase trae N [tabletas/ml/…]", según `unidades_por_presentacion` y la presentación de dispensación del producto. Calcula y muestra el total en unidad mínima: 5 envases × 10 tabletas = 50 tabletas. Esa cantidad convertida es la que entra al inventario.
  - **Implemento deportivo** y **Material de oficina:** se compra **por unidad** (balones, raquetas, cuadernos, cajas de lápices). Si el producto tiene presentación por caja, aplica la misma lógica que medicamentos.
- Los textos de ayuda, los placeholders y el resumen del detalle deben usar la unidad correcta en cada caso.
- **Precio del producto:** al elegir un producto, el formulario debe traer su **precio vigente** desde `precio_producto` (el registro más reciente). Hoy funciona con medicamentos pero no con alimentos.
  - Encuentra la causa (filtro por tipo, relación, unidad o JS) y corrígela para **todos** los tipos.
  - El precio debe mostrarse en la unidad de compra correspondiente: por kg, por envase o por unidad.
- **Productos de deportes y oficina:** crea las vistas de **Implementos deportivos** y **Material de oficina**, equivalentes a Productos y Medicamentos, para registrarlos y que aparezcan en compras.
  - Implementos deportivos va en el sidebar de deportes.
  - Material de oficina va donde corresponda según el módulo que lo gestione.
- Pruebas: el formulario devuelve el precio y la unidad correctos para un producto de cada tipo.

# Fase 2 — Módulo de deportes: reglas de negocio corregidas

## Reglas de negocio (fuente de verdad)
1. **Las actividades son esporádicas por defecto:** un evento ocurre en una fecha concreta, con hora de inicio y fin, en un espacio. **No se repiten semanalmente.**
2. **Excepción, actividades recurrentes:** por ejemplo, el equipo de fútbol de la universidad entrena ciertos días de la semana a ciertas horas. Se registran como actividad **recurrente**: días de la semana, rango de fechas desde/hasta y horario. Cada día que toca es una **sesión** con su propia asistencia.
3. **Una actividad puede ser de una sección:** por ejemplo, Informática 631 hace una jornada de 7:00 a 12:00 con fútbol, baloncesto y voleibol **a la vez en canchas distintas**.
   - Una actividad o jornada puede tener **varios bloques simultáneos**: disciplina + espacio + hora.
   - Puede asociarse opcionalmente a una sección o grupo: texto libre o PNF + código de sección, según lo que exista.
   - Debes **validar choques**: un mismo espacio no puede tener dos bloques que se solapen en la misma fecha y hora, ni siquiera de actividades distintas.
4. **Torneos:** las personas se inscriben **en los equipos**, no en el torneo.
   - Elimina el apartado "Inscritos" del torneo. Los participantes del torneo son los integrantes de sus equipos.
   - Un equipo se arma agregando integrantes **por cédula**.
5. **Actividades no competitivas (no torneo):** pueden tener inscripción individual por cédula y un cupo opcional.

## Asistencia, el punto más importante
- La asistencia se toma **sobre un evento o sesión concreto**: una fecha y un bloque de una actividad, o una fecha de un torneo.
- **Se puede registrar después del evento.** Si terminó tarde, se carga al día siguiente o días después. No obligues a que sea hoy.
- Pantalla de asistencia:
  1. El usuario elige el evento desde una lista clara de **eventos recientes y de hoy**: nombre, disciplina, espacio, fecha y hora. Incluye un buscador. **No** se elige primero un "horario" y luego una fecha que debe coincidir.
  2. **Marcar presentes con un buscador por cédula**: se escribe la cédula, presiona Enter, aparece el nombre, se marca presente y el campo queda listo para la siguiente.
     - También acepta pegar varias cédulas a la vez, una por línea o separadas por coma, porque al coordinador le entregan una lista en papel.
     - Si la persona estaba inscrita o era integrante de un equipo, se marca como presente.
     - Si **no** estaba inscrita, se permite agregarla como asistente no inscrito, con aviso. La persona debe existir en la base de datos.
     - Cada cédula no encontrada se informa claramente.
  3. Muestra en vivo la lista de presentes y el conteo "presentes X de Y inscritos".
  4. **Cerrar asistencia:** los inscritos o integrantes no marcados pasan a **inasistentes**. Una asistencia cerrada puede reabrirse por quien tenga el permiso `editar`, y eso se registra en la bitácora.
- **Reporte de asistencia por evento:** presentes, inasistentes y no inscritos que asistieron.
- **Reporte histórico por persona:** solo tiene sentido en actividades **recurrentes** (cuántas sesiones asistió de las que hubo). En los eventos esporádicos no muestres porcentajes de asistencia regular.
- Pruebas:
  - Registrar asistencia de un evento de ayer.
  - Pegar 5 cédulas, una inexistente.
  - Cerrar y verificar inasistentes.
  - Actividad recurrente con 3 sesiones y su reporte.
  - Choque de espacio rechazado.

## Otros arreglos del módulo
- **Home de deportes:** crea el inicio del módulo (lo que ve el usuario al entrar a deportes), con:
  - Eventos de hoy y próximos.
  - Préstamos activos y vencidos.
  - Sanciones activas.
  - Stock de implementos con alerta de stock mínimo.
  - Accesos rápidos a prestar, devolver, tomar asistencia y crear actividad.
  Sigue el estilo de los home de otros módulos.
- **Horarios:** rediseña la vista con el estilo del resto del sistema. Recomendado: un calendario o agenda semanal por espacio que muestre los bloques ocupados, con lista alternativa en móvil.
- **Inscripciones:** revisa cómo funcionan hoy y adáptalas a las reglas anteriores:
  - Actividad individual → inscripción por cédula.
  - Torneo → integrantes de equipos.
  - Explica en la propia pantalla cómo se usa.
- **Carteles en PDF:** ajústalos a la nueva estructura. Un cartel por actividad o jornada debe listar todos sus bloques: disciplina, espacio, fecha y hora. El de torneo muestra fecha, espacio y cómo inscribir un equipo.
- **Revisión general:** revisa el resto del módulo (préstamos, sanciones, solvencia, inscripciones) con estas reglas y corrige cualquier error de lógica o de código. Mantén lo ya probado de préstamos y sanciones.
- **Datos existentes:** migra los horarios recurrentes que existan a la nueva estructura. Si no tienen sentido como recurrentes, conviértelos en eventos esporádicos y documéntalo.
- **Seeders de ejemplo:**
  - Una jornada esporádica de una sección con 3 bloques simultáneos.
  - Un torneo con 2 equipos.
  - Un entrenamiento recurrente.
  - Préstamos de ejemplo.

# Fase 3 — Reporte

Crea `docs/deportes/REPORTE_DEPORTES_COMPRAS.md` con estas secciones:
1. Resumen por fase.
2. Cambios de base de datos y su justificación frente al DER del profesor.
3. Explicación sencilla del nuevo flujo de actividades, torneos y asistencia, redactada como para un usuario final, para usarla de manual.
4. Decisiones tomadas.
5. Cómo probar cada punto manualmente.
6. Lista de commits.

Al terminar, deja el árbol de trabajo limpio y escribe en la terminal un resumen corto con la ruta del reporte.
