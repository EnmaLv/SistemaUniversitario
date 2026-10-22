# Mapeo: modelado propio (`diagrama.dbml`) → DER del profesor (`DERProfesor.txt`)

Columna "Tipo de cambio":
- **Renombrar**: la tabla existe en ambos modelos con el mismo propósito; cambia el nombre de la tabla y de las columnas, que llevan el sufijo de 3 letras del profesor.
- **Reestructurar**: además del nombre, cambia la estructura (columnas que se agregan o se quitan, o una FK que apunta a otra tabla).
- **Sin equivalente**: el profesor no la tiene. Se conserva y se justifica.

## Configuración, geografía y estructura

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `estados` | `estado` | Reestructurar | El profesor agrega `pai_id` (tabla nueva `pais`) |
| `municipios` | `municipio` | Renombrar | `estado_id` → `esd_id` |
| `localidads` | `localidad` | Reestructurar | El profesor inserta `parroquia` entre municipio y localidad: `localidad.par_id` en lugar de `municipio_id` |
| — | `pais`, `parroquia` | Nueva | Hay que crearlas y sembrarlas |
| `sede` | `sede` | Reestructurar | Agrega `siglas_sed`, `uni_id` (tabla `universidad`), `loc_id`, etc. |
| `pnf` | `programa` | Reestructurar | `id_pnf` → `id_prg`; agrega `codigo_prg` (unique, not null) |
| `estatus` | `status` | Reestructurar | El profesor usa `status_id` en **todas** las tablas |
| `departamentos` | `departamento` | Renombrar | |
| `artamento` | — | Eliminar | Es un duplicado por error de tipeo en el dbml |
| `configuracion_sistema` | `configuracion_sistema` | Renombrar columnas | `clave_parametro` → `clave_csi`, etc. Agrega `tipo_dato_csi` |
| `exchange_rates` | — | Sin equivalente | Tasa BCV; la usan los productos |
| `perfil` | — | Sin equivalente | El profesor diferencia con las tablas `estudiante`/`profesor` |

## Personas, usuarios y seguridad

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `persona` | `persona` | Reestructurar | `id_persona` → `id_per`, `cedula_persona` → `cedula_per` (unique), `nombre_persona` → `nombres_per`… El teléfono pasa a la tabla `telefono_persona`. La edad se calcula, no se guarda |
| `usuario` | `user` | Reestructurar | `id_usuario` → `id_use`. El login pasa a ser `email_use` o `username_use`. Ya trae `intentos_fallidos_use`, `bloqueado_hasta_use`, `ultimo_login_use` |
| `direccion` | columnas de `persona` | Reestructurar | Pasa a `persona.direccion_per` + `persona.loc_id` |
| `persona_pnf` | `estudiante` (+ `inscripcion`) | Reestructurar | El profesor modela al estudiante aparte, con `sed_id`, cohorte y lapso |
| `rol` | `rol` | Renombrar | `nombre` → `nombre_rol`, agrega `dep_id` |
| `rol_usuario` | `usuario_rol` | Renombrar | Existe además `rol_usuario_contexto` (rol por sede/programa) |
| `permiso` / `vista` / `rol_permiso` / `usuario_permiso` | iguales | Renombrar columnas | Ya están alineadas en estructura (trabajo previo) |
| `modulos` | `modulo` | Renombrar | El profesor no tiene `key`; se conserva como columna propia |
| `rol_modulo`, `usuario_modulo` | — | Sin equivalente | El control de acceso por módulo es propio del sistema |
| `bitacora` | `bitacora_sistema` | Renombrar | Las columnas extra de auditoría ya están justificadas en el reporte de seguridad |
| `archivos` | — | Sin equivalente | Registro de los Excel importados |
| `conversations`, `messages`, `chat_notificaciones_pendientes`, `notifications` | — | Sin equivalente | Chat y notificaciones de Laravel |

## Inventario

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `unidades` | `unidad_medida` | Renombrar | |
| `categorias` | `categoria_producto` | Renombrar | |
| `tipo_producto` | `tipo_producto` | Reestructurar | El profesor usa `modulo_origen_tpr` (texto) en lugar de `modulo_id` |
| `envase_primario` | `envase_primario` | Renombrar columnas | |
| `proveedors` | `proveedor` | Renombrar | Agrega `rif_prv` |
| `productos` | `producto` | Reestructurar | El profesor no tiene `precio_compra` (va en `precio_producto` / `lote`) |
| `precio_productos` | `precio_producto` | Renombrar | |
| `lotes` | `lote` | Renombrar | |
| `inventario_sede_lotes` | `inventario_sede_lote` | Renombrar | |
| `inventario_total` | `inventario_total` | **Falta implementar** | |
| `movimiento_inventarios` | `movimiento_inventario` | Renombrar | |
| `compras` | `compra` | Reestructurar | El profesor usa `sed_id` en lugar de `modulo_id` |
| `detalle_compras` | `detalle_compra` | Renombrar | |

## Comedor

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `recetas` | `receta` | Renombrar | |
| `receta_ingredientes` | `receta_ingrediente` | Renombrar | |
| `consumo_diarios` | `consumo_diario` | Reestructurar | Agrega `sed_id` |
| `consumo_diario_detalles` | `consumo_diario_detalle` | Renombrar | |
| `detalle_registro_diarios` | `detalle_registro_comedor` | Reestructurar | Agrega `sed_id` |
| `registro_diario_c` | `registro_comedor` | Reestructurar | Agrega `sed_id`; el profesor no usa `id_persona_pnf` |
| `sobrantes_comedor` | `sobrante_comedor` | Reestructurar | Agrega `sed_id` |

## Salud

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `consultorios` | `consultorio` | Renombrar | |
| `horario_consultorios` | `horario_consultorio` | Reestructurar | El profesor usa `medico_per_id` (persona) en lugar de `id_rol_usuario` |
| `enfermedades` | `enfermedad` | Renombrar | |
| `consulta` | `consulta_medica` | Renombrar | |
| `recetas_medicas` | `receta_medica` | Renombrar | |
| `detalle_recetas_medicas` | `detalle_receta_medica` | Renombrar | |
| `dispensacion` | `dispensacion_medicamento` | Renombrar | |

## Psicología

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `estado_animos` | `estado_animo` | Renombrar | |
| `avances_sesion` | `avance_sesion` | Renombrar | |
| `estado_animo_diarios` | `estado_animo_diario` | Reestructurar | El profesor usa `paciente_per_id` (persona) en lugar de usuario |
| `grupos_horarios` | `grupo_horario_atencion` | Renombrar | |
| `horarios` | `horario_profesional` | Renombrar | |
| `historia_plantillas_globales` | `plantilla_historia_clinica` | Renombrar | |
| `historia_plantillas_secciones` | `seccion_plantilla_historia` | Renombrar | |
| `nota_evolucion_campos` | `campo_nota_evolucion` | Renombrar | |
| `prioridades` | `prioridad_atencion` | Renombrar | |
| `historia_clinicas` | `historia_clinica` | Reestructurar | `paciente_per_id` (persona) en lugar de `user_id` |
| `historia_secciones_personalizadas` | `historia_seccion_personalizada` | Renombrar | |
| `historia_segmentos_personalizados` | `historia_segmento_personalizado` | Renombrar | |
| `historia_enfermedad` | `historia_enfermedad` | Renombrar columnas | |
| `citas` | `cita_psicologica` | Reestructurar | El profesor **no tiene** las columnas del flujo de propuestas (`bloques_sugeridos`, `bloque_propuesto`, `propuesta_estado`, etc.) ni `status` boolean. Deben conservarse: el sistema las usa |
| `cita_nota_evolucion` | `cita_nota_evolucion` | Renombrar columnas | |
| `publicaciones` | `noticia` (parcial) | Sin equivalente directo | `noticia` no tiene tipo, media ni alcance; conservar |
| `publicacion_reacciones` | — | Sin equivalente | |

## Deportes, transporte y becas

| Tabla actual | Tabla del profesor | Tipo de cambio | Observaciones |
|---|---|---|---|
| `deporte_espacio`, `deporte_actividad`, `deporte_calendario`, `deporte_horario`, `deporte_inscripcion`, `deporte_torneo`, `deporte_torneo_equipo`, `deporte_torneo_integrante`, `deporte_prestamo`, `deporte_sancion` | `espacio_deportivo`, `actividad_deportiva`, `calendario_deportivo`, `horario_deportivo`, `inscripcion_deportiva`, `torneo_deportivo`, `equipo_torneo_deportivo`, `integrante_equipo_deporte`, `prestamo_deportivo`, `sancion_deportiva` | Renombrar | Verificar si el módulo de deportes ya existe en el código |
| `marcas`, `modelos`, `tipo_combustibles`, `bus_paradas`, `bus_rutas`, `bus_ruta_paradas`, `ruta_horarios`, `vehiculos`, `bus_viajes`, `viaje_pasajeros`, `bus_gps_logs`, `carga_combustibles`, `mantenimientos` | `marca_vehiculo`, `modelo_vehiculo`, `tipo_combustible`, `parada_transporte`, `ruta_transporte`, `ruta_parada_transporte`, `horario_ruta_transporte`, `vehiculo_transporte`, `viaje_transporte`, `pasajero_viaje_transporte`, `log_gps_transporte`, `carga_combustible`, `mantenimiento_vehiculo` | Renombrar | |
| `tipo_beneficio`, `be_beneficios`, `be_beca_preguntas`, `be_beca_pregunta_opciones`, `be_beneficio_criterios`, `jornada_beca`, `be_jornada_criterios`, `be_solicitud_becas`, `beca_asignada`, `solicitud_documento` | `tipo_beneficio_estudiantil`, `beneficio_estudiantil`, `pregunta_beneficio`, `opcion_pregunta_beneficio`, `criterio_beneficio`, `jornada_beneficio`, `criterio_jornada_beneficio`, `solicitud_beneficio`, `beneficio_asignado`, `documento_solicitud_beneficio` | Renombrar | En `solicitud_beneficio` el profesor usa `per_id` (persona) en lugar de usuario |
| `be_lapsos` | `lapso_academico` (+ `ano_academico`, `tipo_periodo`) | Reestructurar | Cambian las columnas: `es_actual`/`permite_solicitudes` → `fase_operativa_laa` |
| `condicion_estudiante` | — | Sin equivalente directo | Se acerca a `historial_estatus_estudiante` |

## Diferencias transversales del DER del profesor

1. **Motor de base de datos:** el profesor diseñó para **PostgreSQL y Laravel 11** (`timestamptz`, `jsonb`); el sistema usa MySQL 8 y Laravel 10. En MySQL se mapean como `timestamp` y `json`.
2. **Columnas de auditoría en todas las tablas:** `status_id`, `deleted_at` (soft delete), `created_id` y `updated_id`.
3. **Nomenclatura:** PK `id_xxx`, FK `xxx_id`, columnas con sufijo `_xxx` de 3 letras.
4. **Inconsistencias dentro del propio DER, para comentar con el profesor:**
   - La tabla `qr_validated_generated_titulo` está definida dos veces.
   - Hay `Ref` a tablas que no existen: `pnf.id_pnf` (la tabla se llama `programa`) y `qr_validated_generated`.
   - `Ref: persona.sed_id > sede.id_sed`, pero la tabla `persona` no tiene columna `sed_id`. Esa columna es necesaria para el multisede; hay que confirmar con el profesor si debe existir.
   - `tramite_cambio_pnf` y `convalidacion` definen `pnf_origen_id`/`pnf_destino_id`, pero sus `Ref` usan `prg_origen_id`/`prg_destino_id`.
   - `tipo_producto.modulo_origen_tpr` es texto libre en lugar de una FK a `modulo`.
5. **`user` es palabra reservada en PostgreSQL:** si el sistema migra a PostgreSQL, ese nombre de tabla obliga a usar comillas en todas las consultas.
