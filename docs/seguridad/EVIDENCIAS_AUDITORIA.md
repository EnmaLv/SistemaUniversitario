# Evidencias para auditoría — Sistema de Bienestar Estudiantil (UPTP)

Consultas SQL listas para ejecutar en MySQL 8 durante una auditoría (usan funciones propias de MySQL como `IF`, `GROUP_CONCAT` y `JSON_EXTRACT`; en PostgreSQL se reemplazan por `CASE`, `STRING_AGG` y el operador `->>`). Todas son de solo lectura, salvo la sección 6, que intenta modificar la bitácora para demostrar que **no** se puede.

Cómo abrir una consola de MySQL en el entorno Docker:

```bash
docker exec -it mysqlbienestraestudiantil mysql -u admin -p bienestraestudiantil
```

Reemplaza los valores entre `:` (por ejemplo `:usuario`, `:desde`) antes de ejecutar.

---

## 1. Intentos fallidos de inicio de sesión por usuario

Resumen por username intentado (incluye usernames inexistentes, que quedan con `use_id` nulo):

```sql
SELECT
    COALESCE(b.nombre_usuario_bis, '(sin username)')                AS username_intentado,
    IF(b.use_id IS NULL, 'inexistente', 'existente')        AS tipo_cuenta,
    SUM(b.tipo_evento_bis = 'login_fallido')                              AS intentos_fallidos_use,
    SUM(b.tipo_evento_bis = 'login_bloqueado')                            AS intentos_durante_bloqueo,
    SUM(b.tipo_evento_bis = 'login_exitoso')                              AS inicios_exitosos,
    COUNT(DISTINCT b.direccion_ip_bis)                                 AS ips_distintas,
    MIN(b.created_at)                                            AS primer_evento,
    MAX(b.created_at)                                            AS ultimo_evento
FROM bitacora_sistema b
WHERE b.tipo_evento_bis IN ('login_fallido', 'login_bloqueado', 'login_exitoso')
GROUP BY b.nombre_usuario_bis, tipo_cuenta
ORDER BY intentos_fallidos_use DESC;
```

Detalle cronológico de un usuario:

```sql
SELECT created_at, tipo_evento_bis, nivel_bis, direccion_ip_bis, navegador_bis, sistema_operativo_bis, observacion_bis
FROM bitacora_sistema
WHERE nombre_usuario_bis = ':usuario'
  AND tipo_evento_bis IN ('login_fallido', 'login_bloqueado', 'login_exitoso', 'login_limite_ip',
                 'llave_maestra_incorrecta', 'llave_maestra_correcta', 'logout', 'sesion_expirada')
ORDER BY created_at;
```

Estado actual de los contadores en la tabla `user`:

```sql
SELECT id_use, username_use, intentos_fallidos_use, intentos_llave_fallidos_use,
       bloqueos_temporales_use, ventana_bloqueos_desde_use, ultimo_login_use, ultimo_login_ip_use
FROM user
WHERE intentos_fallidos_use > 0 OR intentos_llave_fallidos_use > 0 OR bloqueos_temporales_use > 0
ORDER BY intentos_fallidos_use DESC;
```

## 2. Bloqueos

Cuentas bloqueadas en este momento:

```sql
SELECT id_use, username_use,
       IF(bloqueado_use, 'permanente', 'temporal') AS tipo,
       motivo_bloqueo_use, bloqueado_en_use, bloqueado_hasta_use,
       bloqueos_temporales_use, ultimo_login_use, ultimo_login_ip_use
FROM user
WHERE bloqueado_use = 1 OR bloqueado_hasta_use > NOW()
ORDER BY bloqueado_en_use DESC;
```

Historial de bloqueos y desbloqueos (quién desbloqueó y desde dónde):

```sql
SELECT b.created_at, b.tipo_evento_bis, b.nivel_bis,
       u.username_use                       AS cuenta_afectada,
       b.nombre_usuario_bis                 AS registrado_por,
       b.direccion_ip_bis, b.observacion_bis
FROM bitacora_sistema b
LEFT JOIN user u ON u.id_use = b.registro_id_bis
WHERE b.tipo_evento_bis IN ('bloqueo_temporal', 'bloqueo_permanente', 'desbloqueo', 'sesion_cerrada_por_bloqueo')
ORDER BY b.created_at DESC;
```

Desbloqueos de emergencia hechos desde la consola (`php artisan seguridad:desbloquear`):

```sql
SELECT created_at, registro_id_bis, observacion_bis
FROM bitacora_sistema
WHERE tipo_evento_bis = 'desbloqueo' AND use_id IS NULL AND observacion_bis LIKE 'consola%'
ORDER BY created_at DESC;
```

## 3. Accesos denegados (403) por IP

```sql
SELECT direccion_ip_bis,
       COUNT(*)                       AS accesos_denegados,
       COUNT(DISTINCT use_id)     AS usuarios_distintos,
       GROUP_CONCAT(DISTINCT nombre_usuario_bis ORDER BY nombre_usuario_bis SEPARATOR ', ') AS usuarios,
       MIN(created_at)                AS primero,
       MAX(created_at)                AS ultimo
FROM bitacora_sistema
WHERE tipo_evento_bis = 'acceso_denegado'
GROUP BY direccion_ip_bis
ORDER BY accesos_denegados DESC;
```

Vistas y acciones que más se intentaron sin permiso:

```sql
SELECT ruta_bis, observacion_bis, COUNT(*) AS intentos
FROM bitacora_sistema
WHERE tipo_evento_bis = 'acceso_denegado'
GROUP BY ruta_bis, observacion_bis
ORDER BY intentos DESC
LIMIT 50;
```

Usuarios bloqueados por accesos restringidos repetidos:

```sql
SELECT b.created_at, u.username_use, b.direccion_ip_bis, b.observacion_bis
FROM bitacora_sistema b
JOIN user u ON u.id_use = b.registro_id_bis
WHERE b.tipo_evento_bis = 'bloqueo_permanente'
  AND JSON_UNQUOTE(JSON_EXTRACT(b.datos_nuevos_bis, '$.motivo_bloqueo_use')) = 'accesos_restringidos_repetidos'
ORDER BY b.created_at DESC;
```

## 4. Cambios a roles y permisos

Permisos y módulos otorgados o retirados a roles:

```sql
SELECT b.created_at, b.nombre_usuario_bis AS realizado_por, b.tipo_evento_bis, b.observacion_bis,
       JSON_EXTRACT(b.datos_nuevos_bis, '$.permisos_otorgados') AS permisos_otorgados,
       JSON_EXTRACT(b.datos_anteriores_bis,  '$.permisos_quitados')  AS permisos_quitados,
       JSON_EXTRACT(b.datos_nuevos_bis, '$.modulos_agregados')  AS modulos_agregados,
       JSON_EXTRACT(b.datos_anteriores_bis,  '$.modulos_quitados')   AS modulos_quitados
FROM bitacora_sistema b
WHERE b.tipo_evento_bis IN ('rol_permisos_asignados', 'rol_permisos_actualizados', 'rol_permisos_retirados')
ORDER BY b.created_at DESC;
```

Permisos directos (grant/revoke) y módulos especiales por usuario:

```sql
SELECT b.created_at, b.nombre_usuario_bis AS realizado_por, b.observacion_bis AS usuario_afectado,
       b.datos_anteriores_bis, b.datos_nuevos_bis
FROM bitacora_sistema b
WHERE b.tipo_evento_bis = 'permisos_usuario_actualizados'
ORDER BY b.created_at DESC;
```

Roles asignados o retirados a usuarios:

```sql
SELECT b.created_at, b.nombre_usuario_bis AS realizado_por, b.tipo_evento_bis, b.observacion_bis,
       JSON_EXTRACT(b.datos_nuevos_bis, '$.roles_agregados') AS roles_agregados,
       JSON_EXTRACT(b.datos_anteriores_bis,  '$.roles_quitados')  AS roles_quitados
FROM bitacora_sistema b
WHERE b.tipo_evento_bis IN ('roles_usuario_actualizados', 'roles_usuario_retirados', 'roles_usuario_registro')
ORDER BY b.created_at DESC;
```

Creación, edición y eliminación de roles (cambios Eloquent sobre la tabla `rol`):

```sql
SELECT created_at, nombre_usuario_bis, tipo_evento_bis, registro_id_bis, datos_anteriores_bis, datos_nuevos_bis, observacion_bis
FROM bitacora_sistema
WHERE tabla_afectada_bis = 'rol'
ORDER BY created_at DESC;
```

## 5. Actividad de un usuario en un rango de fechas

```sql
SELECT created_at, modulo_bis, tipo_evento_bis, nivel_bis, metodo_http_bis, ruta_bis, codigo_respuesta_bis,
       tabla_afectada_bis, registro_id_bis, direccion_ip_bis, navegador_bis, sistema_operativo_bis, observacion_bis
FROM bitacora_sistema
WHERE (nombre_usuario_bis = ':usuario' OR use_id = (SELECT id_use FROM user WHERE username_use = ':usuario'))
  AND created_at BETWEEN ':desde 00:00:00' AND ':hasta 23:59:59'
ORDER BY created_at;
```

Resumen por módulo y acción del mismo periodo:

```sql
SELECT modulo_bis, tipo_evento_bis, COUNT(*) AS eventos
FROM bitacora_sistema
WHERE use_id = (SELECT id_use FROM user WHERE username_use = ':usuario')
  AND created_at BETWEEN ':desde 00:00:00' AND ':hasta 23:59:59'
GROUP BY modulo_bis, tipo_evento_bis
ORDER BY eventos DESC;
```

Sesiones distintas usadas por el usuario (el identificador se guarda como hash SHA-256, nunca en claro):

```sql
SELECT session_id_bis, MIN(created_at) AS inicio, MAX(created_at) AS fin, COUNT(*) AS eventos,
       GROUP_CONCAT(DISTINCT direccion_ip_bis) AS ips
FROM bitacora_sistema
WHERE use_id = (SELECT id_use FROM user WHERE username_use = ':usuario')
  AND session_id_bis IS NOT NULL
GROUP BY session_id_bis
ORDER BY inicio DESC;
```

## 6. Prueba de inmutabilidad de la bitácora

Triggers instalados:

```sql
SHOW TRIGGERS WHERE `Table` = 'bitacora_sistema';
```

Resultado esperado: `trg_bitacora_sistema_sin_update` (BEFORE UPDATE) y `trg_bitacora_sistema_sin_delete` (BEFORE DELETE). En PostgreSQL el equivalente es `trg_bitacora_sistema_inmutable` con la función `fn_bitacora_sistema_inmutable()`.

Intento de modificación. Ambas sentencias **deben fallar** con `ERROR 1644 (45000): La bitacora es de solo insercion ...` y no deben alterar ninguna fila:

```sql
SELECT id_bis, tipo_evento_bis, created_at FROM bitacora_sistema ORDER BY id_bis DESC LIMIT 1;

UPDATE bitacora_sistema SET tipo_evento_bis = 'alterada' ORDER BY id_bis DESC LIMIT 1;
DELETE FROM bitacora_sistema ORDER BY id_bis DESC LIMIT 1;

SELECT id_bis, tipo_evento_bis, created_at FROM bitacora_sistema ORDER BY id_bis DESC LIMIT 1;
```

La primera y la última consulta deben devolver exactamente la misma fila.

Continuidad de los identificadores (un hueco indicaría filas borradas por fuera de los triggers, por ejemplo con `DROP`/`TRUNCATE` o restaurando un respaldo):

```sql
SELECT a.id + 1 AS inicio_hueco, MIN(b.id) - 1 AS fin_hueco
FROM bitacora_sistema a
JOIN bitacora b ON b.id > a.id
GROUP BY a.id
HAVING inicio_hueco < MIN(b.id)
ORDER BY inicio_hueco;
```

Nota: `AUTO_INCREMENT` puede dejar huecos legítimos cuando una transacción que insertó en la bitácora se revierte. Si aparece un hueco, compáralo con la hora del evento y con los logs de la aplicación (`storage/logs/laravel.log`) antes de concluir que hubo manipulación.

La protección también existe en la aplicación: el modelo `App\Models\Bitacora` lanza `LogicException` si se intenta `save()` sobre un registro existente, `delete()`, `forceDelete()` o `destroy()`. Lo verifican las pruebas `tests/Feature/Seguridad/BitacoraTest.php`.
