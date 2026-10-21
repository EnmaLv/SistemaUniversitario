# CLAUDE.md — Sistema de Bienestar Estudiantil (UPTP)

## Idioma y estilo
- Responde y documenta siempre en español neutral, con tono directo y sin introducciones de cortesía.
- No agregues comentarios que no sean funcionales en el código. No pongas comentarios decorativos, separadores tipo `// ═══` ni explicaciones obvias.
- Nombres de clases, métodos, variables, tablas y rutas en español, siguiendo el estilo existente.
- No inventes archivos, tablas ni rutas que no existan. Si necesitas confirmar algo, léelo del código. Si no existe, decláralo en el reporte final.

## Stack
- Laravel 10 (los controladores usan `$this->middleware()` en el constructor), PHP 8.
- MySQL 8 en Docker.
- Blade + Tailwind CSS + Alpine.js. **Bootstrap está prohibido**: fue eliminado del sistema y no existe.
- El paquete AdminLTE sigue instalado solo por `FilterInterface` en `app/AdminLTE/Filters/ModuleFilter.php`; no se usa para la UI.
- SweetAlert2 está disponible en algunas vistas (`Swal`). Siempre deja un fallback con `confirm()`.
- Sistema operativo del desarrollador: Linux CachyOS (KDE Plasma, Wayland).

## Entorno Docker
- Contenedor de la app: `SisgestiondeBienestraEstudiantil`.
- Contenedor de MySQL: `mysqlbienestraestudiantil`. Dentro de la red Docker usa el puerto 3306; desde el host, el 3307.
- **Todos los comandos `php artisan`, `composer` y `phpunit` se ejecutan dentro del contenedor.** El PHP del host no tiene `pdo_mysql` y el host no resuelve el nombre del contenedor de MySQL:

  ```bash
  docker exec -it SisgestiondeBienestraEstudiantil php artisan <comando>
  ```

  Si `artisan` no se encuentra, averigua la carpeta del proyecto dentro del contenedor y usa `-w <ruta>`.
- La base de datos es de desarrollo. `migrate:fresh --seed` está permitido.
- Orden de seeders: `ModulosSeeder` → `DefaultRolesSeeder` → `PermisosSeeder` → resto.

## Convenciones de UI
- Layout: `<x-app-layout>`. Alertas: `@include('components.alert')`.
- Tablas: `<x-table-row :id="...">` y `<x-table-actions :id="..." baseUrl="..." />`.
- Colores con variables CSS: `var(--bg-card)`, `var(--border-color)`, `var(--text-main)`, `var(--text-muted)`, `var(--input-bg)`, `var(--input-border)`, `var(--color-primary)`.
- Botón primario: `bg-red-800 hover:bg-red-900 text-white rounded-xl font-extrabold`.
- Iconos: Font Awesome (`fas fa-*`).
- Copia la estructura visual de `resources/views/admin/configuracion/roles/index.blade.php` para nuevas pantallas de listado.

## Modelo de datos clave
- `usuario` (PK `id_usuario`, login por `username`), `persona` (PK `id_persona`), `rol` (PK `id_rol`, SoftDeletes, `nombre`, `slug`), `rol_usuario` (`id_rol`, `id_usuario`), `modulos` (`id`, `key`).
- El modelo `App\Models\Usuario` tiene mutators que **hashean** `password` y `master_key`. Asígnales texto plano, nunca `Hash::make(...)`, o quedarán doblemente hasheados.
- Las columnas JSON `rol.menu_permissions` y `usuario.extra_permissions` **ya no existen**. No las vuelvas a usar.

## Sistema de permisos (RBAC granular)
- Tablas: `vista`, `permiso` (`key` = `vista.accion`), `rol_permiso`, `usuario_permiso` (grant/revoke con vigencia), `usuario_modulo`, `rol_modulo`.
- Acciones: `ver`, `crear`, `editar`, `eliminar`, `activar`, `exportar` (`config/permisos.php`).
- Catálogo de vistas: `config/menu_permissions.php`. Patrones de ruta por vista: `config/menu_routes.php`.
  - Al agregar una vista o ruta nueva, regístrala en ambos archivos y ejecuta `php artisan permisos:sincronizar`.
- Servicios en `app/Support/Permisos/`: `ResolutorAccion`, `MapaRutas`, `SincronizadorPermisos`, `CatalogoPermisos`, `VersionPermisos`.
  - Después de cualquier cambio de roles o permisos llama a `VersionPermisos::invalidar()`.
- `Usuario`: `esSuperAdmin()`, `puede($vista, $accion)`, `puedeAlguna([...], $accion)`, `puedeRuta($nombreRuta)`, `canAccessMenu($keys)`, `vistasVisibles()`.
- Blade: `@canMenu`, `@canAction('vista', 'accion')`, `@canRoute('nombre.ruta')`, `@canModule('key')`.
- Middleware `App\Http\Middleware\CheckMenuPermission`: protege el grupo `/admin` y responde 403 si falta la acción.
- Superadmins: roles `Administrador` y `Secretaria de Bienestar` (`config('permisos.roles_superadmin')`).

## Flujo de autenticación
- Usuarios normales: `App\Http\Controllers\Auth\LoginController`.
- Administradores: paso intermedio de Llave Maestra en `App\Http\Controllers\Auth\AdminMasterKeyController`.
- Las pantallas de configuración exigen además el middleware `RequireMasterKey` (`session('master_key_validated')`).
- Redirección inicial: `ModuleFilter::resolveInitialRoute()`, usada en LoginController, AdminMasterKeyController y `RedirectIfAuthenticated::redirectUsing()` en `AppServiceProvider`.
  - Con un solo módulo permitido, entra directo; con varios, va al selector.
  - **No cambies esta lógica** salvo que sea estrictamente necesario para seguridad, y justifícalo en el reporte.

## Restricción académica (DER del profesor)
- El profesor integró el sistema en uno mayor (`DERProfesor.txt` en la documentación del proyecto).
- **Crear tablas nuevas está permitido.** Modificar nomenclatura o estructura de tablas que existen en el DER del profesor requiere justificación escrita en el reporte, para que el estudiante lo hable con él.
- Referencias del DER del profesor útiles para seguridad:
  - Tabla `bitacora_sistema`: `modulo_bis`, `tipo_evento_bis`, `tabla_afectada_bis`, `nombre_usuario_bis`, `datos_anteriores_bis`, `datos_nuevos_bis`, `direccion_ip_bis`, `navegador_bis`, `sistema_operativo_bis`, `observacion_bis`, `use_id`, `registro_id_bis`.
  - Tabla `user`: `intentos_fallidos_use`, `bloqueado_hasta_use`, `ultimo_login_use`, `status_id`.
- El diseño propio del estudiante (`diagrama.dbml`) define la tabla `bitacora` como **solo INSERT**, con estas columnas:
  - `modulo`, `accion`, `tabla_afectada`, `registro_id`, `usuario_id`, `nombre_usuario`.
  - `datos_anteriores`, `datos_modificados`.
  - `ip_address`, `navegador`, `sistema_operativo`, `fecha_hora`.
  - Índices sobre `usuario_id`, `modulo` y `fecha_hora`.

## Git
- Trabaja siempre en una rama propia, nunca directamente en `principal`/`main`.
- Haz commits pequeños por fase, con mensajes en español en imperativo.
- No hagas push salvo que se te pida.
