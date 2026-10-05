<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

// Inicio (Home)
Breadcrumbs::for('home', function (Trail $trail) {
    $trail->push('Inicio', route('home'));
});

// Maestros > Categorías
Breadcrumbs::for('admin.maestros.categorias.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Categorías', route('admin.maestros.categorias.index'));
});

Breadcrumbs::for('admin.maestros.categorias.create', function (Trail $trail) {
    $trail->parent('admin.maestros.categorias.index');
    $trail->push('Crear', route('admin.maestros.categorias.create'));
});

Breadcrumbs::for('admin.maestros.categorias.show', function (Trail $trail, $categoria) {
    $trail->parent('admin.maestros.categorias.index');
    $trail->push('Ver Mas', route('admin.maestros.categorias.show', $categoria));
});

Breadcrumbs::for('admin.maestros.categorias.edit', function (Trail $trail, $categoria) {
    $trail->parent('admin.maestros.categorias.index');
    $trail->push('Editar', route('admin.maestros.categorias.edit', $categoria));
});

// Maestros > Sedes
Breadcrumbs::for('admin.maestros.sedes.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Sedes', route('admin.maestros.sedes.index'));
});

Breadcrumbs::for('admin.maestros.sedes.create', function (Trail $trail) {
    $trail->parent('admin.maestros.sedes.index');
    $trail->push('Crear', route('admin.maestros.sedes.create'));
});

Breadcrumbs::for('admin.maestros.sedes.show', function (Trail $trail, $sede) {
    $trail->parent('admin.maestros.sedes.index');
    $trail->push('Ver Mas', route('admin.maestros.sedes.show', $sede));
});

Breadcrumbs::for('admin.maestros.sedes.edit', function (Trail $trail, $sede) {
    $trail->parent('admin.maestros.sedes.index');
    $trail->push('Editar', route('admin.maestros.sedes.edit', $sede));
});

// Maestros > Productos
Breadcrumbs::for('admin.maestros.productos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Productos', route('admin.maestros.productos.index'));
});


Breadcrumbs::for('admin.maestros.productos.create', function (Trail $trail) {
    $trail->parent('admin.maestros.productos.index');
    $trail->push('Crear', route('admin.maestros.productos.create'));
});

Breadcrumbs::for('admin.maestros.productos.show', function (Trail $trail, $producto) {
    $trail->parent('admin.maestros.productos.index');
    $trail->push('Ver Mas', route('admin.maestros.productos.show', $producto));
});

Breadcrumbs::for('admin.maestros.productos.edit', function (Trail $trail, $producto) {
    $trail->parent('admin.maestros.productos.index');
    $trail->push('Editar', route('admin.maestros.productos.edit', $producto));
});

// Maestros > Proveedores
Breadcrumbs::for('admin.maestros.proveedores.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Proveedores', route('admin.maestros.proveedores.index'));
});

Breadcrumbs::for('admin.maestros.proveedores.create', function (Trail $trail) {
    $trail->parent('admin.maestros.proveedores.index');
    $trail->push('Crear', route('admin.maestros.proveedores.create'));
});

Breadcrumbs::for('admin.maestros.proveedores.show', function (Trail $trail, $proveedor) {
    $trail->parent('admin.maestros.proveedores.index');
    $trail->push('Ver Mas', route('admin.maestros.proveedores.show', $proveedor));
});

Breadcrumbs::for('admin.maestros.proveedores.edit', function (Trail $trail, $proveedor) {
    $trail->parent('admin.maestros.proveedores.index');
    $trail->push('Editar', route('admin.maestros.proveedores.edit', $proveedor));
});

// Maestros > Recetas
Breadcrumbs::for('admin.maestros.recetas.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Recetas', route('admin.maestros.recetas.index'));
});

Breadcrumbs::for('admin.maestros.recetas.create', function (Trail $trail) {
    $trail->parent('admin.maestros.recetas.index');
    $trail->push('Crear', route('admin.maestros.recetas.create'));
});

Breadcrumbs::for('admin.maestros.recetas.edit', function (Trail $trail, $receta) {
    $trail->parent('admin.maestros.recetas.index');
    $trail->push('Editar', route('admin.maestros.recetas.edit', $receta));
});

// Mestros > Receta Ingredientes
Breadcrumbs::for('admin.maestros.receta_ingredientes.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Ingredientes de Recetas', route('admin.maestros.receta_ingredientes.index'));
});

Breadcrumbs::for('admin.maestros.receta_ingredientes.create', function (Trail $trail) {
    $trail->parent('admin.maestros.receta_ingredientes.index');
    $trail->push('Crear', route('admin.maestros.receta_ingredientes.create'));
});

Breadcrumbs::for('admin.maestros.receta_ingredientes.edit', function (Trail $trail, $ingrediente) {
    $trail->parent('admin.maestros.receta_ingredientes.index');
    $trail->push('Editar', route('admin.maestros.receta_ingredientes.edit', $ingrediente));
});

// Movimientos > Compras
Breadcrumbs::for('admin.movimientos.compras.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Compras', route('admin.movimientos.compras.index'));
});

Breadcrumbs::for('admin.movimientos.compras.create', function (Trail $trail) {
    $trail->parent('admin.movimientos.compras.index');
    $trail->push('Crear', route('admin.movimientos.compras.create'));
});

Breadcrumbs::for('admin.movimientos.compras.edit', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.compras.index');
    $trail->push('Editar', route('admin.movimientos.compras.edit', $id));
});

Breadcrumbs::for('admin.movimientos.compras.show', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.compras.index');
    $trail->push('Ver Mas', route('admin.movimientos.compras.show', $id));
});

Breadcrumbs::for('admin.movimientos.compras.enviarCorreo', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.compras.index');
    $trail->push('Enviar Correo', route('admin.movimientos.compras.enviarCorreo', $id));
});

// Movimientos > Lotes
Breadcrumbs::for('admin.movimientos.lotes.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Lotes', route('admin.movimientos.lotes.index'));
});

Breadcrumbs::for('admin.movimientos.lotes.create', function (Trail $trail) {
    $trail->parent('admin.movimientos.lotes.index');
    $trail->push('Crear', route('admin.movimientos.lotes.create'));
});

Breadcrumbs::for('admin.movimientos.lotes.edit', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.lotes.index');
    $trail->push('Editar', route('admin.movimientos.lotes.edit', $id));
});

Breadcrumbs::for('admin.movimientos.lotes.show', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.lotes.index');
    $trail->push('Ver Mas', route('admin.movimientos.lotes.show', $id));
});

Breadcrumbs::for('admin.movimientos.registro_diario.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Registro Diario', route('admin.movimientos.registro_diario.index'));
});

Breadcrumbs::for('admin.movimientos.registro_diario.show', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.registro_diario.index');
    $trail->push('Ver Registro', route('admin.movimientos.registro_diario.show', $id));
});

// Movimientos > sedes por Lotes
Breadcrumbs::for('admin.movimientos.sedes_lotes', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Lotes por Sedes', route('admin.movimientos.sedes_lotes'));
});

Breadcrumbs::for('admin.movimientos.sedes_lotes.show', function (Trail $trail, $id) {
    $trail->parent('admin.movimientos.sedes_lotes');
    $trail->push('Ver Mas', route('admin.movimientos.sedes_lotes.show', $id));
});

// Movimientos > Historial de Movimientos
Breadcrumbs::for('admin.movimientos.historial_movimientos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Historial de Movimientos', route('admin.movimientos.historial_movimientos.index'));
});

Breadcrumbs::for('admin.movimientos.registro_comida.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Registro Comida', route('admin.movimientos.registro_comida.index'));
});

Breadcrumbs::for('admin.maestros.pnf.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Pnf', route('admin.maestros.pnf.index'));
});

Breadcrumbs::for('admin.maestros.pnf.create', function (Trail $trail) {
    $trail->parent('admin.maestros.pnf.index');
    $trail->push('Crear', route('admin.maestros.pnf.create'));
});

Breadcrumbs::for('admin.maestros.pnf.edit', function (Trail $trail, $id) {
    $trail->parent('admin.maestros.pnf.index');
    $trail->push('Editar', route('admin.maestros.pnf.edit', $id));
});

// Estado
Breadcrumbs::for('admin.estado.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Estado', route('admin.estado.index'));
});

// Municipio
Breadcrumbs::for('admin.municipio.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Municipio', route('admin.municipio.index'));
});

// Localidad
Breadcrumbs::for('admin.localidad.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Localidad', route('admin.localidad.index'));
});

// Consultas > Reportes
Breadcrumbs::for('admin.consultas.reportes.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Reportes', route('admin.consultas.reportes.index'));
});

Breadcrumbs::for('admin.configuracion.persona.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Estudiante', route('admin.configuracion.persona.index'));
});


Breadcrumbs::for('admin.configuracion.persona.create', function (Trail $trail) {
    $trail->parent('admin.configuracion.persona.index');
    $trail->push('Crear Estudiante', route('admin.configuracion.persona.create'));
});


// Configuración > Ver Persona
Breadcrumbs::for('admin.configuracion.persona.show', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.persona.index');
    $trail->push('Ver Mas', route('admin.configuracion.persona.show', $id));
});

// Configuración > Editar Persona
Breadcrumbs::for('admin.configuracion.persona.edit', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.persona.index');
    $trail->push('Editar Estudiante', route('admin.configuracion.persona.edit', $id));
});

// Configuración > Indexar
Breadcrumbs::for('admin.configuracion.indexar.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Indexar', route('admin.configuracion.indexar.index'));
});

//Configuracion Empleados
Breadcrumbs::for('admin.configuracion.empleados.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Empleados', route('admin.configuracion.empleados.index'));
});

Breadcrumbs::for('admin.configuracion.empleados.show', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.empleados.index');
    $trail->push('Ver Empleado', route('admin.configuracion.empleados.show', $id));
});

Breadcrumbs::for('admin.configuracion.empleados.edit', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.empleados.index');
    $trail->push('Editar Empleado', route('admin.configuracion.empleados.edit', $id));
});

//Configuracion Permisos
Breadcrumbs::for('admin.configuracion.permisos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Permisos', route('admin.configuracion.permisos.index'));
});

Breadcrumbs::for('admin.configuracion.permisos.edit', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.permisos.index');
    $trail->push('Editar Permiso', route('admin.configuracion.permisos.edit', $id));
});

//Configuracion Roles
Breadcrumbs::for('admin.configuracion.roles.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Roles', route('admin.configuracion.roles.index'));
});

Breadcrumbs::for('admin.configuracion.roles.create', function (Trail $trail) {
    $trail->parent('admin.configuracion.roles.index');
    $trail->push('Crear Rol', route('admin.configuracion.roles.create'));
});

Breadcrumbs::for('admin.configuracion.roles.edit', function (Trail $trail, $id) {
    $trail->parent('admin.configuracion.roles.index');
    $trail->push('Editar Rol', route('admin.configuracion.roles.edit', $id));
});

//Configuracion Archivo
Breadcrumbs::for('admin.configuracion.archivos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Archivo', route('admin.configuracion.archivos.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.agenda.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Agenda', route('admin.psicologia.maestros.agenda.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.agenda.estadisticas', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.agenda.index');
    $trail->push('Estadisticas', route('admin.psicologia.maestros.agenda.estadisticas'));
});

Breadcrumbs::for('admin.psicologia.maestros.avances_sesion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Avances', route('admin.psicologia.maestros.avances_sesion.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.avances_sesion.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.avances_sesion.index');
    $trail->push('Crear Avances', route('admin.psicologia.maestros.avances_sesion.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.avances_sesion.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.avances_sesion.index');
    $trail->push('Editar Avances', route('admin.psicologia.maestros.avances_sesion.edit', $id));
});

Breadcrumbs::for('admin.psicologia.maestros.campos_evolucion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Campos de Evoluciòn', route('admin.psicologia.maestros.campos_evolucion.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.campos_evolucion.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.campos_evolucion.index');
    $trail->push('Crear Campos de Evoluciòn', route('admin.psicologia.maestros.campos_evolucion.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.campos_evolucion.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.campos_evolucion.index');
    $trail->push('Editar Campos de Evoluciòn', route('admin.psicologia.maestros.campos_evolucion.edit', $id));
});

Breadcrumbs::for('admin.psicologia.maestros.citas.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Citas', route('admin.psicologia.maestros.citas.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.citas.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.citas.index');
    $trail->push('Crear Citas', route('admin.psicologia.maestros.citas.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.citas.edit_note', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.citas.index');
    $trail->push('Notas de Cita', route('admin.psicologia.maestros.citas.edit_note'));
});

Breadcrumbs::for('admin.psicologia.maestros.citas.show', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.citas.index');
    $trail->push('Ver mas Citas', route('admin.psicologia.maestros.citas.show'));
});

Breadcrumbs::for('admin.psicologia.maestros.estado_animos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Estado de Animo', route('admin.psicologia.maestros.estado_animos.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.estado_animos.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.estado_animos.index');
    $trail->push('Crear Estado de Animo', route('admin.psicologia.maestros.estado_animos.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.estado_animos.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.estado_animos.index');
    $trail->push('Editar Estado de Animo', route('admin.psicologia.maestros.estado_animos.edit', $id));
});


Breadcrumbs::for('admin.psicologia.maestros.grupos_horarios.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Grupo de Horarios', route('admin.psicologia.maestros.grupos_horarios.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.grupos_horarios.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.grupos_horarios.index');
    $trail->push('Crear Grupo de Horarios', route('admin.psicologia.maestros.grupos_horarios.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.grupos_horarios.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.grupos_horarios.index');
    $trail->push('Editar Grupo de Horarios', route('admin.psicologia.maestros.grupos_horarios.edit', $id));
});

Breadcrumbs::for('admin.psicologia.maestros.grupos_horarios.show', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.grupos_horarios.index');
    $trail->push('Editar Grupo de Horarios', route('admin.psicologia.maestros.grupos_horarios.show'));
});


Breadcrumbs::for('admin.psicologia.maestros.historias.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Historias', route('admin.psicologia.maestros.historias.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.historias.show', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.historias.index');
    $trail->push('Editar Historias', route('admin.psicologia.maestros.historias.show', $id));
});


Breadcrumbs::for('admin.psicologia.maestros.horarios.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Horarios', route('admin.psicologia.maestros.horarios.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.horarios.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.horarios.index');
    $trail->push('Crear Horarios', route('admin.psicologia.maestros.horarios.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.horarios.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.horarios.index');
    $trail->push('Editar Horarios', route('admin.psicologia.maestros.horarios.edit', $id));
});

Breadcrumbs::for('admin.psicologia.maestros.horarios.show', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.horarios.index');
    $trail->push('Editar Show', route('admin.psicologia.maestros.horarios.show'));
});


Breadcrumbs::for('admin.psicologia.maestros.plantillas.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Anexos', route('admin.psicologia.maestros.plantillas.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.plantillas.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.plantillas.index');
    $trail->push('Crear Anexos', route('admin.psicologia.maestros.plantillas.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.plantillas.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.plantillas.index');
    $trail->push('Editar Anexos', route('admin.psicologia.maestros.plantillas.edit', $id));
});


Breadcrumbs::for('admin.psicologia.maestros.plantillas_globales.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Plantillas Globales', route('admin.psicologia.maestros.plantillas_globales.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.plantillas_globales.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.plantillas_globales.index');
    $trail->push('Crear Plantillas Globales', route('admin.psicologia.maestros.plantillas_globales.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.plantillas_globales.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.plantillas_globales.index');
    $trail->push('Editar Plantillas Globales', route('admin.psicologia.maestros.plantillas_globales.edit', $id));
});


Breadcrumbs::for('admin.psicologia.maestros.prioridades.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Prioridades', route('admin.psicologia.maestros.prioridades.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.prioridades.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.prioridades.index');
    $trail->push('Crear Prioridades', route('admin.psicologia.maestros.prioridades.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.prioridades.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.prioridades.index');
    $trail->push('Editar Prioridades', route('admin.psicologia.maestros.prioridades.edit', $id));
});


Breadcrumbs::for('admin.psicologia.maestros.publicaciones.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Publicaciones', route('admin.psicologia.maestros.publicaciones.index'));
});

Breadcrumbs::for('admin.psicologia.maestros.publicaciones.create', function (Trail $trail) {
    $trail->parent('admin.psicologia.maestros.publicaciones.index');
    $trail->push('Crear Publicaciones', route('admin.psicologia.maestros.publicaciones.create'));
});

Breadcrumbs::for('admin.psicologia.maestros.publicaciones.edit', function (Trail $trail, $id) {
    $trail->parent('admin.psicologia.maestros.publicaciones.index');
    $trail->push('Editar Publicaciones', route('admin.psicologia.maestros.publicaciones.edit', $id));
});

Breadcrumbs::for('admin.psicologia.maestros.publicaciones.mural', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Mural', route('admin.psicologia.maestros.publicaciones.mural'));
});


Breadcrumbs::for('admin.enfermedades.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Enfermedades', route('admin.enfermedades.index'));
});
