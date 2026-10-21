# Evidencias para auditoría — Sistema de Bienestar Estudiantil (UPTP)

Consultas SQL listas para ejecutar en MySQL 8 durante una auditoría. Todas son de solo lectura, salvo la sección 6, que intenta modificar la bitácora para demostrar que **no** se puede.

Cómo abrir una consola de MySQL en el entorno Docker:

```bash
docker exec -it mysqlbienestraestudiantil mysql -u admin -p bienestraestudiantil
```

Reemplaza los valores entre `:` (por ejemplo `:usuario`, `:desde`) antes de ejecutar.

---

## 1. Intentos fallidos de inicio de sesión por usuario

Resumen por username intentado (incluye usernames inexistentes, que quedan con `usuario_id` nulo):

```sql
SELECT
    COALESCE(b.nombre_usuario, '(sin username)')                AS username_intentado,
    IF(b.usuario_id IS NULL, 'inexistente', 'existente')        AS tipo_cuenta,
    SUM(b.accion = 'login_fallido')                              AS intentos_fallidos,
    SUM(b.accion = 'login_bloqueado')                            AS intentos_durante_bloqueo,
    SUM(b.accion = 'login_exitoso')                              AS inicios_exitosos,
    COUNT(DISTINCT b.ip_address)                                 AS ips_distintas,
    MIN(b.fecha_hora)                                            AS primer_evento,
    MAX(b.fecha_hora)                                            AS ultimo_evento
FROM bitacora b
WHERE b.accion IN ('login_fallido', 'login_bloqueado', 'login_exitoso')
GROUP BY b.nombre_usuario, tipo_cuenta
ORDER BY intentos_fallidos DESC;
```

Detalle cronológico de un usuario:

```sql
SELECT fecha_hora, accion, nivel, ip_address, navegador, sistema_operativo, observacion
FROM bitacora
WHERE nombre_usuario = ':usuario'
  AND accion IN ('login_fallido', 'login_bloqueado', 'login_exitoso', 'login_limite_ip',
                 'llave_maestra_incorrecta', 'llave_maestra_correcta', 'logout', 'sesion_expirada')
ORDER BY fecha_hora;
```

Estado actual de los contadores en la tabla `usuario`:

```sql
SELECT id_usuario, username, intentos_fallidos, intentos_llave_fallidos,
       bloqueos_temporales, ventana_bloqueos_desde, ultimo_login, ultimo_login_ip
FROM usuario
WHERE intentos_fallidos > 0 OR intentos_llave_fallidos > 0 OR bloqueos_temporales > 0
ORDER BY intentos_fallidos DESC;
```

## 2. Bloqueos

Cuentas bloqueadas en este momento:

```sql
SELECT id_usuario, username,
       IF(bloqueado, 'permanente', 'temporal') AS tipo,
       motivo_bloqueo, bloqueado_en, bloqueado_hasta,
       bloqueos_temporales, ultimo_login, ultimo_login_ip
FROM usuario
WHERE bloqueado = 1 OR bloqueado_hasta > NOW()
ORDER BY bloqueado_en DESC;
```

Historial de bloqueos y desbloqueos (quién desbloqueó y desde dónde):

```sql
SELECT b.fecha_hora, b.accion, b.nivel,
       u.username                       AS cuenta_afectada,
       b.nombre_usuario                 AS registrado_por,
       b.ip_address, b.observacion
FROM bitacora b
LEFT JOIN usuario u ON u.id_usuario = b.registro_id
WHERE b.accion IN ('bloqueo_temporal', 'bloqueo_permanente', 'desbloqueo', 'sesion_cerrada_por_bloqueo')
ORDER BY b.fecha_hora DESC;
```

Desbloqueos de emergencia hechos desde la consola (`php artisan seguridad:desbloquear`):

```sql
SELECT fecha_hora, registro_id, observacion
FROM bitacora
WHERE accion = 'desbloqueo' AND usuario_id IS NULL AND observacion LIKE 'consola%'
ORDER BY fecha_hora DESC;
```

## 3. Accesos denegados (403) por IP

```sql
SELECT ip_address,
       COUNT(*)                       AS accesos_denegados,
       COUNT(DISTINCT usuario_id)     AS usuarios_distintos,
       GROUP_CONCAT(DISTINCT nombre_usuario ORDER BY nombre_usuario SEPARATOR ', ') AS usuarios,
       MIN(fecha_hora)                AS primero,
       MAX(fecha_hora)                AS ultimo
FROM bitacora
WHERE accion = 'acceso_denegado'
GROUP BY ip_address
ORDER BY accesos_denegados DESC;
```

Vistas y acciones que más se intentaron sin permiso:

```sql
SELECT ruta, observacion, COUNT(*) AS intentos
FROM bitacora
WHERE accion = 'acceso_denegado'
GROUP BY ruta, observacion
ORDER BY intentos DESC
LIMIT 50;
```

Usuarios bloqueados por accesos restringidos repetidos:

```sql
SELECT b.fecha_hora, u.username, b.ip_address, b.observacion
FROM bitacora b
JOIN usuario u ON u.id_usuario = b.registro_id
WHERE b.accion = 'bloqueo_permanente'
  AND JSON_UNQUOTE(JSON_EXTRACT(b.datos_modificados, '$.motivo_bloqueo')) = 'accesos_restringidos_repetidos'
ORDER BY b.fecha_hora DESC;
```

## 4. Cambios a roles y permisos

Permisos y módulos otorgados o retirados a roles:

```sql
SELECT b.fecha_hora, b.nombre_usuario AS realizado_por, b.accion, b.observacion,
       JSON_EXTRACT(b.datos_modificados, '$.permisos_otorgados') AS permisos_otorgados,
       JSON_EXTRACT(b.datos_anteriores,  '$.permisos_quitados')  AS permisos_quitados,
       JSON_EXTRACT(b.datos_modificados, '$.modulos_agregados')  AS modulos_agregados,
       JSON_EXTRACT(b.datos_anteriores,  '$.modulos_quitados')   AS modulos_quitados
FROM bitacora b
WHERE b.accion IN ('rol_permisos_asignados', 'rol_permisos_actualizados', 'rol_permisos_retirados')
ORDER BY b.fecha_hora DESC;
```

Permisos directos (grant/revoke) y módulos especiales por usuario:

```sql
SELECT b.fecha_hora, b.nombre_usuario AS realizado_por, b.observacion AS usuario_afectado,
       b.datos_anteriores, b.datos_modificados
FROM bitacora b
WHERE b.accion = 'permisos_usuario_actualizados'
ORDER BY b.fecha_hora DESC;
```

Roles asignados o retirados a usuarios:

```sql
SELECT b.fecha_hora, b.nombre_usuario AS realizado_por, b.accion, b.observacion,
       JSON_EXTRACT(b.datos_modificados, '$.roles_agregados') AS roles_agregados,
       JSON_EXTRACT(b.datos_anteriores,  '$.roles_quitados')  AS roles_quitados
FROM bitacora b
WHERE b.accion IN ('roles_usuario_actualizados', 'roles_usuario_retirados', 'roles_usuario_registro')
ORDER BY b.fecha_hora DESC;
```

Creación, edición y eliminación de roles (cambios Eloquent sobre la tabla `rol`):

```sql
SELECT fecha_hora, nombre_usuario, accion, registro_id, datos_anteriores, datos_modificados, observacion
FROM bitacora
WHERE tabla_afectada = 'rol'
ORDER BY fecha_hora DESC;
```

## 5. Actividad de un usuario en un rango de fechas

```sql
SELECT fecha_hora, modulo, accion, nivel, metodo_http, ruta, codigo_respuesta,
       tabla_afectada, registro_id, ip_address, navegador, sistema_operativo, observacion
FROM bitacora
WHERE (nombre_usuario = ':usuario' OR usuario_id = (SELECT id_usuario FROM usuario WHERE username = ':usuario'))
  AND fecha_hora BETWEEN ':desde 00:00:00' AND ':hasta 23:59:59'
ORDER BY fecha_hora;
```

Resumen por módulo y acción del mismo periodo:

```sql
SELECT modulo, accion, COUNT(*) AS eventos
FROM bitacora
WHERE usuario_id = (SELECT id_usuario FROM usuario WHERE username = ':usuario')
  AND fecha_hora BETWEEN ':desde 00:00:00' AND ':hasta 23:59:59'
GROUP BY modulo, accion
ORDER BY eventos DESC;
```

Sesiones distintas usadas por el usuario (el identificador se guarda como hash SHA-256, nunca en claro):

```sql
SELECT session_id, MIN(fecha_hora) AS inicio, MAX(fecha_hora) AS fin, COUNT(*) AS eventos,
       GROUP_CONCAT(DISTINCT ip_address) AS ips
FROM bitacora
WHERE usuario_id = (SELECT id_usuario FROM usuario WHERE username = ':usuario')
  AND session_id IS NOT NULL
GROUP BY session_id
ORDER BY inicio DESC;
```

## 6. Prueba de inmutabilidad de la bitácora

Triggers instalados:

```sql
SHOW TRIGGERS WHERE `Table` = 'bitacora';
```

Resultado esperado: `trg_bitacora_sin_update` (BEFORE UPDATE) y `trg_bitacora_sin_delete` (BEFORE DELETE).

Intento de modificación. Ambas sentencias **deben fallar** con `ERROR 1644 (45000): La bitacora es de solo insercion ...` y no deben alterar ninguna fila:

```sql
SELECT id, accion, fecha_hora FROM bitacora ORDER BY id DESC LIMIT 1;

UPDATE bitacora SET accion = 'alterada' ORDER BY id DESC LIMIT 1;
DELETE FROM bitacora ORDER BY id DESC LIMIT 1;

SELECT id, accion, fecha_hora FROM bitacora ORDER BY id DESC LIMIT 1;
```

La primera y la última consulta deben devolver exactamente la misma fila.

Continuidad de los identificadores (un hueco indicaría filas borradas por fuera de los triggers, por ejemplo con `DROP`/`TRUNCATE` o restaurando un respaldo):

```sql
SELECT a.id + 1 AS inicio_hueco, MIN(b.id) - 1 AS fin_hueco
FROM bitacora a
JOIN bitacora b ON b.id > a.id
GROUP BY a.id
HAVING inicio_hueco < MIN(b.id)
ORDER BY inicio_hueco;
```

Nota: `AUTO_INCREMENT` puede dejar huecos legítimos cuando una transacción que insertó en la bitácora se revierte. Si aparece un hueco, compáralo con la hora del evento y con los logs de la aplicación (`storage/logs/laravel.log`) antes de concluir que hubo manipulación.

La protección también existe en la aplicación: el modelo `App\Models\Bitacora` lanza `LogicException` si se intenta `save()` sobre un registro existente, `delete()`, `forceDelete()` o `destroy()`. Lo verifican las pruebas `tests/Feature/Seguridad/BitacoraTest.php`.
