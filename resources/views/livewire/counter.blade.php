<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+ Agregar</button>
    <button wire:click="decrement">- Quitar</button>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div model:wire.live class="card-header">
                <h3 class="card-title"><b>Productos Registrados</b></h3>

                <div class="card-tools">
                    <a class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 primary" href=" {{ url('admin/maestros/productos/create') }}" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 tool">
                        <i class="fas fa-plus"></i>
                        <b>Crear Nuevo</b>
                    </a>
                </div>
                <!-- /.card-tools -->
            </div>
            <!-- /.card-header -->
            <div class="card-body" style="display: block;">
                <table id="example1" class="table table-bordered table-striped table-hover table-sm" border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Categoria</th>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Descripcion</th>
                            <th>Imagen</th>
                            <th>Precio de Compra</th>
                            <th>Precio de Venta</th>
                            <th>Stock Minimo</th>
                            <th>Stock Maximo</th>
                            <th>Unidad de Medida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr>
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td>{{ $producto->categoria->nombre }}</td>
                                <td>{{ $producto->codigo }}</td>
                                <td>{{ $producto->nombre }}</td>
                                <td>{!! $producto->descripcion !!}</td>
                                <td>
                                    <img src="{{ asset('storage/' . $producto->imagen) }}" class="img-thumbnail"
                                        alt="{{ $producto->nombre }}"
                                        style="width: 150px; height: auto; object-fit: cover;">
                                </td>
                                <td>{{ $producto->precio_compra }}</td>
                                <td>{{ $producto->precio_venta }}</td>
                                <td>{{ $producto->stock_minimo }}</td>
                                <td>{{ $producto->stock_maximo }}</td>
                                <td>{{ $producto->unidad_medida }}</td>
                                <td style="text-align: center;">
                                    @if ($producto->estado)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ url('admin/maestros/productos/' . $producto->id) }}"
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 info"><i class="fas fa-eye"></i></a>
                                    <a href="{{ url('admin/maestros/productos/' . $producto->id . '/edit') }}"
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 warning"><i class="fas fa-edit"></i></a>
                                    <form action="{{ url('admin/maestros/productos/' . $producto->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 danger"
                                            onclick="preguntar{{ $producto->id }}(event)"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                    <script>
                                        function preguntar{{ $producto->id }}(event) {
                                            event.preventDefault();
                                            Swal.fire({
                                                title: '¿Estás seguro?',
                                                text: "No podrás deshacer esta acción",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    event.target.form.submit();
                                                }
                                            });
                                        }
                                    </script>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
