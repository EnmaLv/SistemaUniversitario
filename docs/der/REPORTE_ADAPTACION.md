# Reporte de adaptación al DER del profesor — Sistema de Bienestar Estudiantil (UPTP)

Rama: `feature/adaptacion-der` (creada desde `feature/seguridad-auditoria`). Sin push.
Insumos: `PROMPT_ADAPTACION_DER.md`, `MAPEO_DER.md`, `docs/der/DERProfesor.txt` (copiado desde `~/Escritorio/DERProfesor.txt`, que no estaba en el proyecto), `docs/der/diagrama.dbml` y `docs/seguridad/REPORTE_SEGURIDAD.md`.
Diagrama final generado desde el esquema real: `docs/der/diagrama_adaptado.dbml`.

---

## 1. Resumen ejecutivo

| Fase | Estado | Resultado |
|---|---|---|
| 1. Pendientes de seguridad | Completada | Psicología solo para el rol **Psicólogo** (incluso los superadministradores quedan fuera); cada psicólogo ve solo sus citas, pacientes, historias y notas; historias por par (paciente, psicólogo). Notas certificadas de becas y Excel importados en disco privado con descarga controlada y comando `archivos:migrar-privados`. Corregidos H-36, H-38, H-39, H-40, H-41, H-42, H-44 y un hallazgo nuevo de severidad alta (H-45: cualquier usuario podía avanzar el lapso académico). Quedan documentados H-06, H-23, H-24, H-37 y H-43 (requieren decisión). |
| 2. Cuentas automáticas | Completada | Usuario = cédula, contraseña inicial = cédula, roles Becario y Paciente, cambio obligatorio en el primer inicio (web y API), login con cédula o correo, importación masiva por lotes con Job en cola y resumen, comando `usuarios:generar-faltantes`. |
| 3. Multisede | Completada | Sede activa en sesión, selector para superadministradores (con "Todas"), traits `PerteneceASede` / `PerteneceASedePorRelacion`, columnas `sed_id` faltantes, inventario por sede, listados y reportes filtrados, datos de prueba en dos sedes. |
| 4. `inventario_total` | Completada | `ServicioInventario` (entrada, salida, ajuste, transferencia, recálculo) en una sola transacción con `lockForUpdate`; todo movimiento de stock pasa por él; comando `inventario:recalcular-totales` (se ejecuta en el seeder); alertas de mínimo y máximo. |
| 5. Tablas del modelado propio | Completada | Se implementaron `consumo_diario` y `consumo_diario_detalle` (el comedor ya consumía insumos sin registrarlo). Lo demás eran nombres distintos o tablas de deportes (módulo inexistente). |
| 6. Adaptación al DER | Completada (módulos 1 a 9) | Todas las tablas del sistema con equivalente en el DER usan su nombre y su nomenclatura (`id_xxx`, `xxx_id`, columnas `_xxx`) y las columnas de auditoría (`status_id`, `deleted_at`, `created_id`, `updated_id`). El módulo 10 (deportes) no existe en el código. |
| 7. Reporte | Este documento | Incluye el archivo de ejemplo `docs/der/ejemplo_importacion.csv` (20 filas, 6 inválidas a propósito). |

Verificación final de la rama: `php -l` de todos los archivos tocados, `php artisan route:list` (460 rutas; los nombres no cambiaron, solo se eliminaron rutas que apuntaban a métodos inexistentes), `php artisan migrate:fresh --seed` sin errores y `php artisan test` con 73 pruebas aprobadas (ver sección 6). Pruebas nuevas en esta rama: confidencialidad de psicología, archivos privados, cuentas de la comunidad, multisede, inventario, prueba de humo de todas las pantallas (sin parámetro y con un parámetro, para tres perfiles), flujo de salud, flujo de psicología y API de transporte.

Lo que queda pendiente está en la sección 3.4 ("Pendientes para hablar con el profesor") y en la sección 4 ("Decisiones tomadas sin consultar").

---

## 2. Tabla final de equivalencias

Estado: **Adaptada** = nombre y columnas del DER; **Conservada (justificada)** = la tabla no existe en el DER o se mantuvo una estructura distinta, con la nomenclatura del profesor; **Laravel** = tabla del framework, sin cambios.

### 2.1 Catálogos, geografía y estructura

| Tabla anterior | Tabla actual | Estado | Notas |
|---|---|---|---|
| `estatus` | `status` (`id_sta`, `nombre_sta`, `descripcion_sta`, `modulo_sta`) | Adaptada | Sembrada en su migración con 1 = Activo y 2 = Inactivo (la necesitan las FK por defecto) |
| `pnf` | `programa` (`id_prg`, `codigo_prg` UNIQUE, `nombre_prg`) | Adaptada | `codigo_prg` se genera (`PNF-<NOMBRE>`). Columnas del DER sin uso omitidas (código del ministerio, siglas, gaceta, resolución, arancel, salida intermedia) |
| `departamentos` | `departamento` (`id_dep`, `nombre_dep`, `descripcion_dep`) | Adaptada | `jefe_departamento_pro_id` omitido: no existe tabla `profesor` |
| `configuracion_sistema` | `configuracion_sistema` (`id_csi`, `clave_csi`, `valor_csi`, `tipo_dato_csi`, `descripcion_csi`) | Adaptada | |
| `exchange_rates` | `tasa_cambio` (`id_tca`, `nombre_tca`, `fuente_tca`, `promedio_tca`, `variacion_tca`, `fecha_vigencia_tca`) | Conservada (justificada) | No está en el DER; la usa la actualización de precios con la tasa BCV |
| `perfil` | `perfil` (`id_perfil`, `nombre_perfil`) | Conservada (justificada) | El DER distingue con tablas `estudiante`/`profesor` que el sistema no maneja |
| `estados` | `estado` (`id_esd`, `nombre_esd`, `pai_id`) | Adaptada | |
| — | `pais` (`id_pai`, `nombre_pai`) | Adaptada (nueva) | Sembrada con Venezuela |
| `municipios` | `municipio` (`id_mun`, `nombre_mun`, `esd_id`) | Adaptada | |
| `localidads` | `parroquia` (`id_par`, `nombre_par`, `mun_id`) | Adaptada | Los datos eran parroquias: se adoptó la estructura del profesor |
| — | `localidad` (`id_loc`, `nombre_loc`, `par_id`) | Adaptada (nueva) | Vacía: el sistema no captura localidades |
| — | `universidad` (`id_uni`, …) | Adaptada (nueva, mínima) | Sembrada con la UPTP; omitidas gaceta, decreto, logo, fechas |
| `sede` | `sede` (`id_sed`, `nombre_sed`, `siglas_sed`, `codigo_ministerio_sed`, `direccion_sed`, `telefono_sed`, `correo_sed`, `uni_id`, `loc_id`) | Adaptada | `uni_id` y `loc_id` nullable (ver 3.1) |

### 2.2 Personas, usuarios y seguridad

| Tabla anterior | Tabla actual | Estado | Notas |
|---|---|---|---|
| `persona` | `persona` (`id_per`, `cedula_per` UNIQUE, `nacionalidad_per`, `nombres_per`, `segundo_nombre_per`, `apellidos_per`, `segundo_apellido_per`, `telefono_per`, `genero_per`, `fecha_nacimiento_per`, `correo_per`, `semestre_per`, `id_perfil`, `sed_id`) | Adaptada | `sed_id` creada aunque el DER no la tiene (la pide el multisede). La edad ya no se guarda. El teléfono sigue en persona (ver 3.1) |
| `direccion` | `direccion` (`id_dir`, `sector_dir`, `calle_dir`, `per_id`, `par_id`) | Conservada (justificada) | El DER la pone como columnas de persona; el formulario captura sector y calle por separado |
| `persona_pnf` | `persona_programa` (`id_pep`, `per_id`, `prg_id`, `fecha_inicio_pep`, `fecha_fin_pep`) | Conservada (justificada) | El DER usa `estudiante` + `inscripcion` con cohorte, lapso y malla, que el sistema no maneja. Prefijo `pep` porque el DER ya usa `ppr` para `precio_producto` |
| `usuario` | `user` (`id_use`, `per_id` UNIQUE, `username_use`, `email_use`, `password_use`, `master_key_use`, `intentos_fallidos_use`, `bloqueado_hasta_use`, `ultimo_login_use`, …) | Adaptada | Columnas extra de seguridad con sufijo `_use` (bloqueos, llave maestra, cambio de contraseña obligatorio, chat) |
| `rol` | `rol` (`id_rol`, `nombre_rol`, `slug_rol`, `descripcion_rol`, `dep_id`) | Adaptada | `slug_rol` es propio (lo usa el RBAC) |
| `rol_usuario` | `usuario_rol` (`id_usr`, `rol_id`, `use_id`) | Adaptada | |
| `modulos` | `modulo` (`id_mod`, `key_mod`, `nombre_mod`, `descripcion_mod`, `icono_mod`, `orden_mod`) | Adaptada | `key_mod` es propia (el menú y los permisos la usan) |
| `vista`, `permiso`, `rol_permiso`, `usuario_permiso` | `vista` (`id_vis`, `key_vis`…), `permiso` (`id_pmi`, `nombre_pmi`, `etiqueta_pmi`, `accion_pmi`, `vis_id`, `mod_id`), `rol_permiso` (`id_rop`), `usuario_permiso` (`id_usp`, `tipo_usp`, `fecha_inicio_usp`, `fecha_fin_usp`) | Adaptada | La lógica de permisos no cambió |
| `rol_modulo`, `usuario_modulo` | `rol_modulo` (`id_rmo`), `usuario_modulo` (`id_umo`) | Conservada (justificada) | Control de acceso por módulo, propio del sistema |
| `bitacora` | `bitacora_sistema` (`id_bis`, `modulo_bis`, `tipo_evento_bis`, `tabla_afectada_bis`, `registro_id_bis`, `use_id`, `nombre_usuario_bis`, `datos_anteriores_bis`, `datos_nuevos_bis`, `direccion_ip_bis`, `navegador_bis`, `sistema_operativo_bis`, `observacion_bis`, `created_at`) | Adaptada | Columnas extra de la petición HTTP (ver 3.1). Inmutable por triggers |
| `archivos` | `archivos` | Conservada (justificada) | Registro de los Excel importados; sin equivalente |
| `conversations`, `messages`, `chat_notificaciones_pendientes` | sin cambios | Conservada (justificada) | Chat; sin equivalente |
| `notifications`, `sessions`, `personal_access_tokens`, `cache`, `jobs`, `failed_jobs`, `migrations` | sin cambios | Laravel | `sessions.user_id` y `notifications.notifiable_id` apuntan a `user.id_use` |

### 2.3 Inventario

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `unidades` | `unidad_medida` (`id_unm`, `nombre_unm`, `abreviatura_unm`, `factor_conversion_unm`) | Adaptada |
| `tipo_productos` | `tipo_producto` (`id_tpr`, `nombre_tpr` UNIQUE, `mod_id`) | Adaptada (con `mod_id` en lugar de `modulo_origen_tpr`, ver 3.1) |
| `envase_primarios` | `envase_primario` (`id_enp`, `nombre_enp`) | Adaptada |
| `categorias` | `categoria_producto` (`id_cap`, `nombre_cap`, `descripcion_cap`, `tpr_id`) | Adaptada |
| `productos` | `producto` (`id_pdt`, `codigo_pdt`, `nombre_pdt`, …, `cap_id`, `unm_id`, `enp_id`) | Adaptada (con `precio_compra_pdt` extra) |
| `proveedors` | `proveedor` (`id_prv`, `empresa_prv`, `rif_prv`, `nombre_contacto_prv`, `telefono_prv`, `email_prv`, `direccion_prv`) | Adaptada (`rif_prv` nuevo, capturado en el formulario) |
| `lotes` | `lote` (`id_lot`, `codigo_lote_lot`, `fecha_entrada_lot`, `fecha_vencimiento_lot`, `cantidad_inicial_lot`, `cantidad_actual_lot`, `precio_compra_lot`, `pdt_id`, `prv_id`) | Adaptada |
| `inventario_sede_lotes` | `inventario_sede_lote` (`id_isl`, `cantidad_isl`, `cantidad_convertida_isl`, `sed_id`, `lot_id`) | Adaptada |
| `inventario_totals` (vacía, sin uso) | `inventario_total` (`id_int`, `cantidad_total_int`, `cantidad_convertida_total_int`, `pdt_id`, `sed_id`) | Adaptada e implementada (fase 4) |
| `movimiento_inventarios` | `movimiento_inventario` (`id_moi`, `tipo_movimiento_moi`, `cantidad_moi`, …, `pdt_id`, `lot_id`, `sed_id`, `unm_id`, `use_id`, `mod_id`) | Adaptada (`mod_id` en lugar de `modulo_origen_moi`) |
| `compras` | `compra` (`id_cmp`, `fecha_compra_cmp`, `total_cmp`, `estado_compra_cmp`, `observacion_cmp`, `prv_id`, `sed_id`, `use_id`, `mod_id`) | Adaptada (`mod_id` extra) |
| `detalle_compras` | `detalle_compra` (`id_dcm`, `cantidad_dcm`, `cantidad_convertida_dcm`, `precio_unitario_dcm`, `subtotal_dcm`, `cmp_id`, `pdt_id`, `lot_id`, `unm_id`) | Adaptada |
| `precio_productos` | `precio_producto` (`id_ppr`, `costo_usd_ppr`, `margen_ppr`, `precio_usd_ppr`, `pdt_id`) | Adaptada |

### 2.4 Comedor

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `recetas` | `receta` (`id_rct`, `nombre_rct`, `descripcion_rct`) | Adaptada |
| `receta_ingredientes` | `receta_ingrediente` (`id_rci`, `cantidad_porcion_rci`, `cantidad_convertida_rci`, `rct_id`, `pdt_id`, `unm_id`) | Adaptada |
| `consumo_diarios` | `consumo_diario` (`id_cnd`, `fecha_consumo_cnd`, `porciones_servidas_cnd`, `rct_id`, `sed_id`) | Adaptada e implementada (fase 5) |
| `consumo_diario_detalles` | `consumo_diario_detalle` (`id_cdd`, `cantidad_total_cdd`, `cnd_id`, `pdt_id`, `unm_id`) | Adaptada e implementada (fase 5) |
| `registro_diario_c` | `registro_comedor` (`id_rgc`, `fecha_comedor_rgc`, `hora_comedor_rgc`, `tipo_comida_rgc`, `per_id`, `pep_id`, `sed_id`) | Adaptada (con `tipo_comida_rgc` y `pep_id` extra) |
| `detalle_registro_diarios` | `detalle_registro_comedor` (`id_drc`, `cantidad_servida_drc`, `fecha_drc`, `tipo_comida_drc`, `rct_id`, `sed_id`) | Adaptada (con `tipo_comida_drc` extra) |
| `sobrantes_comedor` | `sobrante_comedor` (`id_sbc`, `fecha_sbc`, `cantidad_sobrante_sbc`, `motivo_sbc`, `accion_tomada_sbc`, `sed_id`) | Adaptada |

### 2.5 Salud

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `consultorios` | `consultorio` (`id_csr`, `nombre_csr`, `descripcion_csr`, `sed_id`) | Adaptada |
| `horario_consultorios` | `horario_consultorio` (`id_hrc`, `dia_hrc`, `hora_inicio_hrc`, `hora_fin_hrc`, `descripcion_hrc`, `csr_id`, `usr_id`) | Adaptada (con `usr_id` en lugar de `medico_per_id`, ver 3.1) |
| `enfermedades` | `enfermedad` (`id_efm`, `codigo_efm`, `nombre_efm`, `categoria_efm`, `nivel_gravedad_efm`) | Adaptada (`categoria_efm` texto) |
| `consultas` | `consulta_medica` (`id_cme`, `fecha_cme`, `motivo_cme`, `observaciones_cme`, `diagnostico_cme`, `paciente_per_id`, `medico_per_id`, `csr_id`, `use_id`) | Adaptada |
| `consulta_enfermedad` | `consulta_enfermedad` (`id_cen`, `cme_id`, `efm_id`) | Conservada (justificada): el DER tiene un único `efm_id` por consulta |
| `recetas_medicas` | `receta_medica` (`id_rme`, `fecha_emision_rme`, `fecha_vigencia_rme`, `descripcion_rme`, `estado_rme`, `cme_id`, `paciente_per_id`, `medico_per_id`) | Adaptada (`estado_rme` extra) |
| `detalle_recetas_medicas` | `detalle_receta_medica` (`id_drm`, `cantidad_drm`, `cantidad_prescrita_drm`, …, `instrucciones_drm`, `rme_id`, `pdt_id`, `unm_id`) | Adaptada |
| `dispensacions` | `dispensacion_medicamento` (`id_dsm`, `cantidad_dsm`, `fecha_dispensacion_dsm`, `observaciones_dsm`, `rme_id`, `drm_id`, `paciente_per_id`, `pdt_id`, `lot_id`, `unm_id`, `sed_id`, `use_id`) | Adaptada |

### 2.6 Psicología

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `estado_animos` | `estado_animo` (`id_esa`, `valor_esa`, `nombre_esa`) | Adaptada |
| `avances_sesion` | `avance_sesion` (`id_avs`, `valor_avs`, `nombre_avs`, `descripcion_avs`, `es_sistema_avs`, `activo_avs`, `psicologo_use_id`) | Adaptada (`activo_avs` extra) |
| `estado_animo_diarios` | `estado_animo_diario` (`id_ead`, `valor_ead`, `fecha_ead`, `paciente_use_id`) | Adaptada con paciente por usuario (ver 3.1) |
| `grupos_horarios` | `grupo_horario_atencion` (`id_gha`, `nombre_gha`, `psicologo_use_id`) | Adaptada |
| `horarios` | `horario_profesional` (`id_hpr`, `dia_hpr`, `hora_inicio_hpr`, `hora_fin_hpr`, `descripcion_hpr`, `gha_id`, `psicologo_use_id`) | Adaptada |
| `historia_plantillas_globales` | `plantilla_historia_clinica` (`id_phc`, `titulo_phc`, `descripcion_phc`, `secciones_phc`, `estado_phc`, `psicologo_use_id`) | Adaptada (`estado_phc` extra) |
| `historia_plantillas_secciones` | `seccion_plantilla_historia` (`id_sph`, `titulo_sph`, `descripcion_general_sph`, `segmentos_sph`, `psicologo_use_id`) | Adaptada |
| `nota_evolucion_campos` | `campo_nota_evolucion` (`id_cne`, `titulo_cne`, `psicologo_use_id`) | Adaptada |
| `prioridades` | `prioridad_atencion` (`id_pat`, `nombre_pat`, `nivel_gravedad_pat`, `psicologo_use_id`) | Adaptada |
| `historia_clinicas` | `historia_clinica` (`id_hcl`, `paciente_use_id`, `psicologo_use_id`, UNIQUE del par) | Adaptada con paciente por usuario |
| `historia_secciones_personalizadas` | `historia_seccion_personalizada` (`id_hsp`, `titulo_hsp`, `descripcion_general_hsp`, `orden_hsp`, `hcl_id`) | Adaptada (`titulo_hsp` extra) |
| `historia_segmentos_personalizados` | `historia_segmento_personalizado` (`id_hsm`, `titulo_hsm`, `subtitulo_hsm`, `icono_hsm`, `contenido_hsm`, `orden_hsm`, `hsp_id`) | Adaptada |
| `historia_enfermedad` | `historia_enfermedad` (`id_hie`, `contexto_hie`, `hcl_id`, `efm_id`) | Adaptada |
| `citas` | `cita_psicologica` (`id_cps`, `motivo_cps`, `fecha_cps`, `hora_cps`, `confirmado_en_cps`, `estado_cita_cps`, `prioridad_texto_cps`, `notas_cps`, `cancelado_por_cps`, `paciente_use_id`, `psicologo_use_id`, `esa_id` + columnas del flujo de propuestas) | Adaptada (columnas de propuestas extra) |
| `cita_nota_evolucion` | `cita_nota_evolucion` (`id_cno`, `contenido_cno`, `cps_id`, `cne_id`) | Adaptada |
| `publicaciones`, `publicacion_reacciones` | sin cambios | Conservada (justificada): `noticia` del DER no tiene tipo, multimedia ni alcance |

### 2.7 Transporte

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `marcas` | `marca_vehiculo` (`id_mav`, `nombre_mav`, `descripcion_mav`) | Adaptada |
| `modelos` | `modelo_vehiculo` (`id_mov`, `nombre_mov`, `descripcion_mov`, `mav_id`) | Adaptada |
| `tipo_combustibles` | `tipo_combustible` (`id_tcb`, `nombre_tcb`, `descripcion_tcb`) | Adaptada |
| `bus_paradas` | `parada_transporte` (`id_ptr`, `nombre_ptr`, `lat_ptr`, `lng_ptr`, `direccion_ptr`) | Adaptada |
| `bus_rutas` | `ruta_transporte` (`id_rtr`, `nombre_rtr`, `distancia_km_rtr`, `descripcion_rtr`, `sed_id`) | Adaptada |
| `bus_ruta_paradas` | `ruta_parada_transporte` (`id_rpt`, `orden_rpt`, `rtr_id`, `ptr_id`) | Adaptada |
| `ruta_horarios` | `horario_ruta_transporte` (`id_hrt`, `hora_salida_hrt`, `tipo_viaje_hrt`, `rtr_id`) | Adaptada |
| `vehiculos` | `vehiculo_transporte` (`id_vtr`, `placa_vtr` UNIQUE, …, `estado_operativo_vtr`, `mov_id`, `tcb_id`, `sed_id`) | Adaptada |
| `bus_viajes` | `viaje_transporte` (`id_vjt`, …, `estado_viaje_vjt`, `vtr_id`, `rtr_id`, `conductor_use_id`) | Adaptada |
| `viaje_pasajeros` | `pasajero_viaje_transporte` (`id_pvt`, `escaneado_at_pvt`, `vjt_id`, `per_id`, `ptr_id`) | Adaptada |
| `bus_gps_logs` | `log_gps_transporte` (`id_lgt`, `local_id_lgt`, `lat_lgt`, `lng_lgt`, `velocidad_lgt`, `heading_lgt`, `registrado_en_lgt`, `origen_lgt`, `vjt_id`) | Adaptada |
| `carga_combustibles` | `carga_combustible` (`id_ccb`, `fecha_ccb`, `litros_ccb`, `precio_litro_ccb`, `total_ccb`, `km_al_cargar_ccb`, `observaciones_ccb`, `vtr_id`, `tcb_id`, `vjt_id`) | Adaptada (`vjt_id` extra) |
| `mantenimientos` | `mantenimiento_vehiculo` (`id_mnv`, `tipo_mnv`, `titulo_mnv`, …, `estado_mnv`, `vtr_id`, `use_id`) | Adaptada |

### 2.8 Becas (beneficios estudiantiles)

| Tabla anterior | Tabla actual | Estado |
|---|---|---|
| `tipo_beneficio` | `tipo_beneficio_estudiantil` (`id_tbe`, `nombre_tbe`, `descripcion_tbe`) | Adaptada (sin pantallas) |
| `be_beneficios` | `beneficio_estudiantil` (`id_bes`, `nombre_beneficio_bes`, `descripcion_bes`, `slug_bes`, `cupones_disponibles_bes`, `cupones_ocupados_bes`, `tbe_id`) | Adaptada |
| `be_beca_preguntas` | `pregunta_beneficio` (`id_pgb`, `codigo_pgb`, `etiqueta_pgb`, `placeholder_pgb`, `ayuda_pgb`, `tipo_campo_pgb`, `obligatoria_pgb`, `valor_min_pgb`, `valor_max_pgb`, `min_length_pgb`, `max_length_pgb`, `regex_pgb`, `orden_pgb`, `bes_id`) | Adaptada |
| `be_beca_pregunta_opciones` | `opcion_pregunta_beneficio` (`id_opb`, `etiqueta_opb`, `valor_opb`, `orden_opb`, `pgb_id`) | Adaptada |
| `be_beneficio_criterios` | `criterio_beneficio` (`id_crb`, `es_eliminatoria_crb`, `operador_crb`, `valor_esperado_crb`, `peso_crb`, `bes_id`, `pgb_id`) | Adaptada |
| `be_jornadas_becas` | `jornada_beneficio` (`id_jnb`, `nombre_jornada_jnb`, `descripcion_jornada_jnb`, `fecha_inicio_solicitud_jnb`, `fecha_fin_solicitud_jnb`, `cupos_maximos_jnb`, `cupos_asignados_jnb`, `bes_id`, `laa_id`) | Adaptada |
| `be_jornada_criterios` | `criterio_jornada_beneficio` (`id_cjb`, `es_eliminatoria_cjb`, `operador_cjb`, `valor_esperado_cjb`, `peso_cjb`, `jnb_id`, `pgb_id`) | Adaptada |
| `be_solicitud_becas` | `solicitud_beneficio` (`id_sob`, `tipo_solicitud_sob`, `estado_solicitud_sob`, `comentario_verificador_sob`, `fecha_verificacion_sob`, `per_id`, `bes_id`, `jnb_id`, `laa_id`, `verificado_por_use_id`) | Adaptada (`estado_solicitud_sob` conserva los códigos 0/1/2) |
| `be_solicitud_respuestas` | `respuesta_solicitud_beneficio` (`id_rsb`, `valor_rsb`, `valor_json_rsb`, `cumple_criterio_rsb`, `sob_id`, `pgb_id`) | Conservada (justificada): el DER no guarda las respuestas del formulario dinámico |
| `solicitud_documento` | `documento_solicitud_beneficio` (`id_dsb`, `nombre_documento_dsb`, `ruta_archivo_dsb`, `tipo_archivo_dsb`, `sob_id`) | Adaptada (archivo en disco privado) |
| `beca_asignada` | `beneficio_asignado` (`id_bfa`, `fecha_inicio_bfa`, `fecha_fin_bfa`, `estado_bfa`, `motivo_revocacion_bfa`, `sob_id`) | Adaptada |
| `be_lapsos` | `lapso_academico` (`id_laa`, `codigo_laa`, `fecha_inicio_laa`, `fecha_fin_laa`, `es_actual_laa`, `permite_solicitudes_laa`) | Conservada con nomenclatura (pendiente): el DER usa `nombre_laa`, `fase_operativa_laa`, `ana_id` y `tpd_id`, pero `ano_academico` y `tipo_periodo` no existen en el sistema |
| `condicion_estudiante` | `condicion_estudiante` (`id_cde`, `nombre_condicion_cde`, `per_id`, `laa_id`) | Conservada (sin pantallas) |

### 2.9 Deportes

Las diez tablas de deportes del modelado propio (`deporte_espacio`, `deporte_actividad`, …) no tienen migración, modelo, controlador ni pantalla: el módulo no existe en el código. No se creó nada; solo se documenta (estado: **pendiente**, no aplica).

---

## 3. Cambios que el profesor debe conocer

### 3.1 Columnas y tablas que el sistema tiene y su DER no, o que se apartan de su estructura

| Dónde | Qué | Justificación |
|---|---|---|
| Todas las tablas | `created_at`/`updated_at`, `status_id`, `deleted_at`, `created_id`, `updated_id` | Son las columnas de auditoría de su DER. `created_id`/`updated_id` no tienen FK porque muchas tablas se crean antes que `user`; se llenan solas desde el usuario autenticado (trait `RegistraAutoria`) |
| `persona.sed_id` | FK a `sede` | Su DER tiene `Ref: persona.sed_id > sede.id_sed` pero no declara la columna; el multisede la necesita |
| `persona.telefono_per` | Un solo teléfono en `persona` | No se creó `telefono_persona`: todos los formularios e importaciones manejan un único teléfono |
| `direccion` | Tabla propia (`sector_dir`, `calle_dir`, `par_id`) | El formulario captura sector y calle por separado y la referencia es a parroquia |
| `persona_programa` | En lugar de `estudiante` + `inscripcion` | Esas tablas exigen cohorte, malla y lapso académico que el sistema no gestiona. Se usa para el PNF del estudiante (comedor, reportes) |
| `user` | Columnas de seguridad con sufijo `_use` (`master_key_use`, `intentos_llave_fallidos_use`, `bloqueos_temporales_use`, `bloqueado_use`, `motivo_bloqueo_use`, `debe_cambiar_password_use`, `password_cambiado_en_use`, `security_questions_use`, …) | Bloqueo por intentos, llave maestra de administración, cambio obligatorio de contraseña y recuperación por preguntas |
| `rol.slug_rol`, `modulo.key_mod`, `rol_modulo`, `usuario_modulo` | Claves y tablas propias del RBAC | El menú, las rutas protegidas y la redirección inicial dependen de ellas |
| `bitacora_sistema` | `metodo_http_bis`, `ruta_bis`, `url_bis`, `codigo_respuesta_bis`, `nivel_bis`, `session_id_bis` | Evidencia de auditoría de cada petición (ver `docs/seguridad/REPORTE_SEGURIDAD.md`). `tipo_evento_bis` es varchar(100) (el DER dice 50) porque los eventos son descriptivos |
| `tasa_cambio` | Tabla propia | Tasa BCV para calcular precios en bolívares |
| `tipo_producto.mod_id`, `movimiento_inventario.mod_id`, `compra.mod_id` | FK a `modulo` en lugar de texto (`modulo_origen_*`) | El sistema separa productos y requisiciones de comedor y de salud por módulo; un texto libre rompe la integridad referencial |
| `producto.precio_compra_pdt` | Último costo | Lo usa la actualización de precios con la tasa |
| `inventario_total.cantidad_convertida_total_int` | Existencia en unidad base (g, ml, unidades) | `cantidad_total_int` está en unidades de presentación (comparable con stock mínimo y máximo); comedor y salud descuentan en unidad base |
| `registro_comedor.tipo_comida_rgc`, `detalle_registro_comedor.tipo_comida_drc` | Desayuno, almuerzo o cena | El comedor maneja tres comidas con ventanas horarias y cupos propios |
| `registro_comedor.pep_id` | Programa del estudiante | Reportes de comensales por PNF |
| `horario_consultorio.usr_id` | FK a `usuario_rol` en lugar de `medico_per_id` | El horario se asigna a una persona en un rol concreto (médico, enfermería…), que es lo que muestran la pantalla y el PDF |
| `consulta_enfermedad` | Varias enfermedades por consulta | Su DER permite una sola (`efm_id`) |
| `receta_medica.estado_rme` | Receta abierta o cerrada | Se cierra cuando se dispensa todo lo prescrito |
| `enfermedad.categoria_efm`, `horario_consultorio.dia_hrc` | Texto (`mental`/`fisica`/`biopsicosocial`, `lunes`…) | Los filtros usan esos valores; `categoria` era un ENUM |
| Psicología: `paciente_use_id` | El paciente se referencia por **usuario**, no por persona (`paciente_per_id`) | Todo el control de confidencialidad se basa en la cuenta autenticada (el paciente dueño solicita, ve y cancela; el psicólogo ve solo sus pacientes) y las notificaciones van a la cuenta. `user.per_id` es UNIQUE, así que la persona se obtiene por relación. **Para hablar con el profesor** |
| `cita_psicologica` | `bloques_sugeridos_cps`, `bloque_propuesto_cps`, `bloques_propuestos_cps`, `propuesta_estado_cps`, `propuesta_bloque_seleccionado_cps`, `motivo_rechazo_propuesta_cps` | Flujo de propuesta y contrapropuesta de horario entre paciente y psicólogo |
| `avance_sesion.activo_avs`, `plantilla_historia_clinica.estado_phc` (1 activa / 2 predeterminada), `historia_seccion_personalizada.titulo_hsp` | Estados y títulos que muestran las pantallas | |
| Transporte: `descripcion_mav`/`descripcion_mov`/`descripcion_tcb`, `carga_combustible.vjt_id` | Descripciones de catálogos y carga asociada al viaje | |
| `archivos`, chat, `publicaciones` | Sin equivalente | Registro de importaciones, mensajería interna y mural de psicología |
| Columnas del DER omitidas | Gaceta, resolución, decretos, logos y fechas de vigencia en `universidad`, `sede` y `programa`; `jefe_departamento_pro_id` | El sistema no las captura; se pueden agregar cuando exista la pantalla |

Becas:

| Dónde | Qué | Justificación |
|---|---|---|
| `pregunta_beneficio` | `placeholder_pgb`, `ayuda_pgb`, `regex_pgb`, límites decimales | Validación del formulario dinámico de postulación |
| `solicitud_beneficio.estado_solicitud_sob` | Códigos 0 pendiente, 1 aprobada, 2 rechazada | Las pantallas y reportes usan esos códigos; el DER usa texto |
| `lapso_academico` | `es_actual_laa`, `permite_solicitudes_laa` en lugar de `fase_operativa_laa`, sin `ana_id`/`tpd_id` | El sistema no gestiona años académicos ni tipos de período |

### 3.2 Inconsistencias encontradas en su DER

1. `qr_validated_generated_titulo` está definida dos veces.
2. Hay `Ref` a tablas que no existen: `pnf.id_pnf` (la tabla se llama `programa`) y `qr_validated_generated`.
3. `Ref: persona.sed_id > sede.id_sed`, pero `persona` no declara `sed_id` (se creó la columna).
4. `tramite_cambio_pnf` y `convalidacion` definen `pnf_origen_id`/`pnf_destino_id`, pero sus `Ref` usan `prg_origen_id`/`prg_destino_id`.
5. `tipo_producto.modulo_origen_tpr` y `movimiento_inventario.modulo_origen_moi` son texto libre en lugar de FK a `modulo`.
6. Prefijo repetido: `ppr` se usa en `precio_producto` y sería natural para `persona_programa`; en el sistema `persona_programa` usa `pep`.
7. `sede.uni_id`, `sede.loc_id` y `universidad.loc_id` son NOT NULL, pero la primera sede puede crearse antes de sembrar la universidad y el sistema no captura localidades; quedaron nullable.
8. `receta_medica.fecha_vigencia_rme` y `descripcion_rme` son opcionales en el DER; el sistema las tenía obligatorias y fallaba al guardar sin vigencia (se alinearon al DER).
9. `lapso_academico` depende de `ano_academico` y `tipo_periodo`, tablas que el sistema no tiene (ver 2.8).

### 3.3 MySQL 8 / Laravel 12 frente a PostgreSQL / Laravel 11

- El DER está escrito para PostgreSQL (`timestamptz`, `jsonb`). En MySQL se usan `timestamp` y `json`; el diagrama adaptado expresa los tipos como en el DER para facilitar la comparación.
- El sistema corre en **Laravel 12** (no 10 ni 11): la estructura de `bootstrap/app.php` (middleware y alias registrados ahí) es la de Laravel 11+, así que no hay incompatibilidad de framework con lo que espera el profesor.
- Los ENUM de MySQL se eliminaron (ver 3.4).

#### Preparación para PostgreSQL

Listo:
- Todo el esquema se crea con el Schema Builder. No quedan `enum()` en las migraciones adaptadas: los estados son texto con validación en la aplicación.
- La bitácora tiene triggers de inmutabilidad para MySQL (`trg_bitacora_sistema_sin_update`, `trg_bitacora_sistema_sin_delete`) y la versión PostgreSQL (función `fn_bitacora_sistema_inmutable()` en plpgsql + trigger `trg_bitacora_sistema_inmutable` BEFORE UPDATE OR DELETE), elegidas con `DB::getDriverName()`.
- `user` es palabra reservada en PostgreSQL: el código siempre pasa por el Query Builder/Eloquent, que la cita; no hay SQL crudo con ese nombre.
- `insertGetId` recibe el nombre de la PK en todas las tablas con PK propia (en PostgreSQL Laravel usa `RETURNING <pk>`; sin el nombre devolvería `id`).
- Se reemplazó SQL propio de MySQL: `GROUP_CONCAT` (chat), `FIELD()` (API de viajes), `IFNULL`, `UPDATE … LIMIT` (prioridades de citas), `COALESCE(created_at, NOW())` en notas de evolución, `SET FOREIGN_KEY_CHECKS` en seeders (ahora `Schema::disableForeignKeyConstraints()`), `LIKE` sobre columnas numéricas.
- Las búsquedas de login comparan con `LOWER()` en ambos lados (PostgreSQL distingue mayúsculas en `=` y `LIKE`).
- La detección de clave duplicada en la API acepta los códigos `23000` (MySQL) y `23505` (PostgreSQL).
- `upsert`/`insertOrIgnore` del inventario se apoyan en índices UNIQUE reales (Laravel genera `ON CONFLICT` en PostgreSQL).

Falta para migrar:
- Ejecutar las migraciones contra PostgreSQL y correr la suite (no hay contenedor PostgreSQL en el entorno).
- Revisar las búsquedas con `LIKE` en pantallas: en PostgreSQL distinguen mayúsculas. Donde importe, usar `ILIKE` o `LOWER()` (los modelos guardan casi todo en mayúsculas, lo que reduce el impacto).
- `docs/seguridad/EVIDENCIAS_AUDITORIA.md` usa `IF`, `GROUP_CONCAT` y `JSON_EXTRACT` (consultas manuales de auditoría para MySQL): en PostgreSQL se reemplazan por `CASE`, `STRING_AGG` y `->>`.
- Algunos `selectRaw`/`orderByRaw` usan funciones estándar (`COUNT`, `SUM`, `COALESCE`, `CASE`, `MIN`, `MAX`, `CONCAT`) que existen en ambos motores; conviene revisarlos al ejecutar contra PostgreSQL.
- Exportar los datos reales de MySQL a PostgreSQL (por ejemplo con `pgloader`) cuando exista un entorno de producción.

### 3.4 Pendientes para hablar con el profesor

1. Pacientes de psicología por usuario (`paciente_use_id`) y no por persona.
2. `persona_programa` en lugar de `estudiante`/`inscripcion`.
3. `lapso_academico` sin `ano_academico`/`tipo_periodo` (ver 2.8).
4. FK a `modulo` en lugar de los textos `modulo_origen_*`.
5. `horario_consultorio` por rol del usuario en lugar de `medico_per_id`.
6. Columnas omitidas del DER (gacetas, resoluciones, localidades).

### 3.5 Pendientes conocidos (comportamiento previo, documentado sin cambiar)

Seguridad (de `docs/seguridad/REPORTE_SEGURIDAD.md`, requieren decisión): H-06 (la lectura de la API de transporte sigue abierta a cualquier token para no romper la app), H-23 (fijar el proxy de confianza en producción), H-24 (rotar las credenciales que quedaron en el historial de git), H-37 (consultas médicas: decidir si cada médico solo edita las suyas), H-43 (`APP_DEBUG`/`LOG_LEVEL` de producción).

Funcionales detectados durante la adaptación:
- Becas: ningún flujo crea `beneficio_asignado` al aprobar (el indicador de becas asignadas queda en 0); "revocar" rechaza la solicitud; los botones "Inactivar" de beneficios, criterios y preguntas apuntan a rutas `DELETE` inexistentes (405); no hay pantalla para dar cupos a un beneficio nuevo; no hay modelo para `tipo_beneficio_estudiantil`.
- Psicología: `CitaController::destroy`/`updateNote` comparan el motivo cifrado con texto plano (la nota manual nunca se detecta); `proponer`/`quitarPropuesta` podrían guardar campos ya descifrados; `PlantillaGlobalController::index` ejecuta `db:seed` desde la web; la rama ajax de avances usa una vista inexistente.
- Salud: dispensación no excluye lotes inactivos; comedor y salud no indican el módulo en los movimientos de inventario.
- Transporte: `motivo_cancelacion`, `consumo_litros_km` y `boca_numero` se capturan pero no tienen columna; la verificación de nombre de parada al editar envía un parámetro distinto al que lee el controlador.
- Vistas que aún usan clases de Bootstrap heredadas (carga de combustible, mantenimiento, detalle de compra y de producto); Bootstrap no se carga, así que se ven sin esos estilos.

---

## 4. Decisiones tomadas sin consultar

### Fase 1 — Seguridad
- El rol **Psicólogo** (slug `psicologo`) no existía: se creó en la migración de roles por defecto y los seeders, con los permisos del módulo `psicologia`. Se sembraron dos psicólogos (`psicologa.laura@example.com` en la sede 1 y `psicologo.carlos@example.com` en la sede 2).
- El middleware `rol.psicologo` (`ExigirRolPsicologo`) se aplica a agenda, citas del psicólogo, historias, plantillas, campos de evolución, prioridades, horarios, grupos, avances y estados de ánimo, además de `CheckMenuPermission`, y también a los superadministradores. Quedan fuera (paciente o compartido): solicitar, ver y cancelar sus propias citas, responder propuestas, mural, publicaciones, estado de ánimo diario, enfermedades (las comparte salud), notificaciones.
- Las historias clínicas pasan a ser por par (paciente, psicólogo): antes había una por paciente y el psicólogo B abría la de A.
- Un usuario que es psicólogo y paciente a la vez: el acceso se decide por la propiedad de la cita, no por el rol.
- El mural (publicaciones) no se considera dato clínico: conserva los permisos normales.
- Las estadísticas generales de psicología solo muestran números agregados (se quitaron los nombres de pacientes).
- Archivos: notas certificadas de becas y Excel importados pasan al disco privado; los Excel antiguos no tenían ruta en la BD y el comando los mueve a `importaciones/legado/`. El comando `archivos:migrar-privados` es idempotente, acepta `--simular` y registra en la bitácora.
- Consultas médicas (H-37): sin cambios, por decisión del dueño.
- Nuevo hallazgo H-45 (alta): la ruta para avanzar el lapso académico no exigía permiso; ahora exige `becas.editar`.

### Fase 2 — Cuentas automáticas
- Prefijo V/E de la cédula: se descarta y se guardan solo los dígitos (como ya hacía el importador). Limitación: `V-12345678` y `E-12345678` colisionan.
- Cédula válida: de 6 a 10 dígitos después de quitar V/E, puntos, espacios y guiones.
- Login: con `@` busca por correo; si no, normaliza la cédula. El límite de intentos usa el identificador normalizado, así que funciona igual con cédula o correo.
- `persona.cedula_per` es UNIQUE (necesario para el upsert portable; el DER ya la tiene única). El `PersonaSeeder` tenía una cédula repetida (31008661): la segunda pasó a 31008663.
- La API también exige el cambio de contraseña: responde 428 mientras esté pendiente.
- La nueva contraseña no puede ser la cédula (con o sin prefijo) ni la actual; mínimo 8 caracteres.
- Importación: lotes de 500 personas en transacción; no pisa datos existentes con vacíos ni cambia la sede o el perfil de personas existentes; un correo inválido se guarda vacío. Cuentas en un Job (cola `database`) en lotes de 200 (bcrypt costo 12, ≈50 s por lote, por debajo de `retry_after`).
- Bitácora: un evento por lote, sin datos personales en `observacion`.

### Fase 3 — Multisede
- Usuario no superadministrador: siempre ve la sede de su persona (la sesión se ignora). Superadministrador: elige la sede o "Todas" (null).
- `persona` no tiene ámbito global por sede (solo el scope `deSedeActiva`): se carga por relaciones en todo el sistema (citas, chat, bitácora) y un filtro global dejaría esas relaciones vacías para personas de otra sede.
- Se reemplazó `FiltroPorSede` (resolvía el usuario una sola vez al arrancar el modelo) por los traits `PerteneceASede` y `PerteneceASedePorRelacion`.
- La compra finalizada entra completa a la sede de la compra (antes se repartía entre todas y el sobrante iba a Acarigua).
- El chat y la búsqueda de usuarios del chat siguen siendo globales (comunicación entre personal).

### Fase 4 — Inventario
- `inventario_sede_lote.cantidad_isl` = unidades de presentación; `cantidad_convertida_isl` = unidad base. `lote.cantidad_actual_lot` = suma de la unidad base en todas las sedes. `inventario_total.cantidad_total_int` = presentación (comparable con mínimo y máximo).
- La merma de vencidos actúa solo sobre la sede activa (todas si el superadministrador eligió "Todas"); el lote queda inactivo cuando ya no tiene existencia en ninguna sede.
- Se corrigió la doble finalización de compras (duplicaba el stock) y el comedor sin bloqueo de filas.

### Fase 5
- `consumo_diario` y `consumo_diario_detalle` existían sin uso: se implementaron (una cabecera por receta servida y un detalle por ingrediente; los movimientos de salida apuntan al consumo).
- Deportes, `tipo_beneficio` y `condicion_estudiante` sin pantallas: solo se documentan.

### Fase 6 — Adaptación
- Se modificaron las migraciones de creación en lugar de agregar migraciones `ALTER`, para que el profesor vea un esquema limpio (la base es de desarrollo). Se renombraron los archivos de migración con el nombre de la tabla nueva.
- `status`: tabla nueva sembrada en su propia migración (1 Activo, 2 Inactivo). Los booleanos `activo`/`estado`/`status` que significaban activo/inactivo pasan a `status_id` 1/2; los "eliminados" (`status = 0`) pasan a borrado lógico (`deleted_at`). Los estados de negocio (estado de una cita, de un viaje, de una compra, de una solicitud) conservan su propia columna con sufijo.
- Macro `Blueprint::auditoria()` para las columnas de auditoría y trait `RegistraAutoria` que llena `created_id`/`updated_id` desde el usuario autenticado.
- Las FK hacia una tabla se renombraron en el módulo de la tabla destino (por ejemplo, `producto_id` → `pdt_id` en recetas de comedor y salud se hizo en el módulo de inventario).
- Nombres de clases, nombres de rutas, claves de menú (`config/menu_routes.php`, `config/menu_permissions.php`) y nombres de campos de formularios no cambiaron. La API de la app móvil conserva su contrato JSON.
- Se mantuvieron las tablas sin equivalente en el DER con la nomenclatura del profesor cuando se tocaron (`tasa_cambio`, `persona_programa`, `consulta_enfermedad`, `respuesta_solicitud_beneficio`, `condicion_estudiante`) y sin cambios las de Laravel y el chat.
- Las pruebas usan una base aparte (`bienestraestudiantil_test`) y se activó `Model::preventAccessingMissingAttributes()` en ellas: cualquier lectura de una columna inexistente hace fallar la prueba (antes devolvía null sin avisar). Con eso y la prueba de humo de pantallas se detectaron y corrigieron decenas de errores previos.
- Se eliminó código muerto sin rutas que dependía de tablas inexistentes: modelo `User` (tabla `users` eliminada), `rol_modulo` (modelo vacío), `RolModuloController`, `BecaBeneficiarioController`, componente Livewire `Counter`, `Compra::finalizarCompra`.
- Rendimiento del entorno: MySQL corre sobre disco mecánico; para acelerar `migrate:fresh` se aplicó `SET GLOBAL innodb_flush_log_at_trx_commit=2` y `sync_binlog=0` (no persistente; se pierde al reiniciar MySQL).

### Módulos de la fase 6 (detalle)
- Inventario: compras y movimientos conservan `mod_id`; la compra finalizada entra solo a la sede de la compra; `insertGetId` con la PK para PostgreSQL.
- Comedor: los seeders asignan programa a los estudiantes (antes ninguno tenía y el comedor rechazaba a todos).
- Salud: `receta_medica.fecha_vigencia_rme` y `descripcion_rme` opcionales como en el DER; filtros de recetas pendientes y completas corregidos (comparaban contra lo dispensado en vez de lo prescrito).
- Psicología: paciente por usuario; "eliminados" como borrado lógico; la fecha de primera cita mostrada al psicólogo es solo la de sus citas.
- Transporte: la API de la app móvil conserva su contrato JSON con un formateador de respuestas; la purga de GPS borra físicamente.
- Becas: se eliminó el código heredado sin rutas que dependía de tablas que no existen (`BecaController`, `BecaService`, `BecaTutorService`, `BecaAsignacionService`, `BecaFormService`, `BecaRequest`, `BecaResource`, `BecaTutor`, `App\Models\Beneficio`, las vistas raíz de `admin/becas` y sus dos pruebas incompletas). La postulación toma beneficio y lapso de la jornada, no de lo que envía el cliente. Editar un beneficio ya no pone sus cupos en cero (cambio de comportamiento: confirmar).

---

## 5. Cómo probar manualmente

Preparación (una sola vez):

```bash
docker exec SisgestiondeBienestraEstudiantil php artisan migrate:fresh --seed
```

La cola (`queue:work`) la arranca `docker/start.sh` al iniciar el contenedor; si se detuvo: `docker exec -d SisgestiondeBienestraEstudiantil php artisan queue:work`.

Cuentas sembradas: `admin@example.com` (Administrador, contraseña `12345678`, llave maestra `masterkey123`), `psicologa.laura@example.com` y `psicologo.carlos@example.com` (Psicólogo, contraseña `12345678`), pacientes y estudiantes del `PersonaSeeder` (por ejemplo `america@example.com`) y la estudiante de la sede Guanare con cédula `30999888`.

### 5.1 Psicólogo A frente a psicólogo B
1. Entra como un paciente y solicita una cita con la psicóloga Laura (Psicología → Citas → Solicitar).
2. Entra como `psicologa.laura@example.com`: la cita aparece en su agenda y en citas pendientes. Confírmala, márcala como realizada y escribe la nota de evolución (antes debe activar su "Esquema general" en Plantillas globales). Abre la historia del paciente.
3. Entra como `psicologo.carlos@example.com`: no ve la cita ni al paciente; si pega la URL de la historia o de la cita de Laura recibe 403.
4. Entra como `admin@example.com`: cualquier pantalla de gestión de psicología (agenda, historias, plantillas) responde 403 aunque tenga todos los permisos; el menú no las muestra. Las estadísticas generales solo muestran totales.
5. Bitácora (Configuración → Bitácora): los intentos de acceso denegado quedan registrados.

### 5.2 Importar un Excel de prueba
1. Como administrador: Configuración → Archivos → subir `docs/der/ejemplo_importacion.csv`.
2. El resumen muestra personas creadas/actualizadas, filas con error y el motivo de cada una (cédula vacía, con letras, muy corta, duplicada en el archivo, sin nombre, sin apellido).
3. El archivo queda "Generando cuentas" mientras el Job crea los usuarios en lotes y pasa a "Procesado" (la pantalla se refresca sola).
4. En la bitácora aparece `importacion_personas` con el resumen y sin datos personales.

### 5.3 Primer inicio de sesión con la cédula
1. Cierra sesión e inicia con una cédula del archivo como usuario y como contraseña (sin puntos ni prefijo; también se acepta `V-12345678`).
2. El sistema obliga a cambiar la contraseña antes de mostrar cualquier otra pantalla; no acepta la cédula ni menos de 8 caracteres.
3. Tras el cambio entra con los roles Becario y Paciente. La bitácora registra `password_cambiado`.
4. Los usuarios existentes siguen entrando con su correo.

### 5.4 Cambio de sede
1. Como superadministrador, usa el selector de sede de la barra superior: elige Guanare, luego "Todas".
2. Inventario, compras, comedor, consultas, consultorios, vehículos y rutas muestran solo los datos de la sede elegida (o todos con "Todas"). Lo que se crea queda en la sede activa.
3. Un usuario que no es superadministrador ve siempre solo su sede y no tiene selector; un POST manual al cambio de sede responde 403.
4. La bitácora registra `sede_activa_cambiada`.

### 5.5 Movimiento de inventario
1. Inventario → Compras: crea una requisición en la sede activa, agrega productos y finalízala. El stock entra solo a esa sede; finalizarla dos veces no duplica el stock.
2. Productos y Existencias por sede muestran el total por sede con alertas de bajo mínimo, agotado o sobre máximo.
3. Comedor → Registro de comida: registra las raciones servidas de una receta; se descuentan los ingredientes de la sede (lotes más próximos a vencer primero) y queda el consumo diario.
4. Salud → Consultas: crea una consulta, la receta y dispénsala; el medicamento se descuenta de la sede y la receta se cierra.
5. Historial de movimientos muestra cada entrada y salida con existencia anterior y final.
6. `docker exec SisgestiondeBienestraEstudiantil php artisan inventario:recalcular-totales` recalcula los totales desde las existencias por lote.

---

## 6. Lista de commits

Commits de la rama `feature/adaptacion-der` sobre `feature/seguridad-auditoria`, en orden:

| Commit | Mensaje |
|---|---|
| `5b95587` | Agrega el encargo de adaptación al DER y sus insumos |
| `056390f` | Cierra los pendientes de seguridad: confidencialidad de psicología y archivos privados |
| `bff64a2` | Crea cuentas automáticas para la comunidad universitaria |
| `3b60972` | Hace el sistema multisede |
| `49acd0b` | Implementa inventario_total y centraliza los movimientos de stock |
| `0025082` | Implementa consumo_diario en el comedor |
| `e98027c` | Agrega una prueba de humo de pantallas y corrige pantallas rotas previas |
| `dc9fecd` | Adapta los catálogos base al DER del profesor |
| `30907c1` | Adapta geografía y sede al DER del profesor |
| `038b4f2` | Adapta persona al DER del profesor (módulo 3, parte 1) |
| `12d4d83` | Adapta usuario, roles y permisos al DER del profesor (módulo 3, parte 2) |
| `95897bb` | Adapta la bitácora al DER del profesor (módulo 3, parte 3) |
| `94d58c0` | Adapta el inventario al DER del profesor (módulo 4) |
| `7404442` | Adapta el comedor al DER del profesor (módulo 5) |
| `a6c56f5` | Adapta salud al DER del profesor (módulo 6) |
| `9573f7d` | Adapta psicología al DER del profesor (módulo 7) |
| `1f6d1b2` | Adapta transporte al DER del profesor (módulo 8) |
| `9094c4c` | Adapta becas al DER del profesor (módulo 9) |
| (este commit) | Agrega el reporte de adaptación y el diagrama adaptado |

Verificación final: `php artisan test` → 73 pruebas aprobadas (413 aserciones); `php artisan migrate:fresh --seed` sin errores; `php artisan route:list` → 460 rutas; `config/menu_routes.php` y `config/menu_permissions.php` idénticos a la rama base.
