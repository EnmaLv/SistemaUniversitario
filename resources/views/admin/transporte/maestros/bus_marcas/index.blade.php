<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Marcas de buses</h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <button type="button" onclick="openModal('modalCrear')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Nueva marca
                </button>
            </div>

            <div class="mb-3 flex flex-col gap-4 rounded-2xl border p-2.5 shadow-sm lg:flex-row lg:items-center" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <form action="{{ route('admin.transporte.maestros.bus_marcas.index') }}" method="GET" class="relative w-full">
                    <input type="hidden" name="estado" value="{{ request('estado', 1) }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400"><i class="fas fa-search text-sm"></i></div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar marca..."
                        class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500"
                        style="background-color:var(--input-bg);border-color:var(--border-color);color:var(--text-main);">
                </form>
                <div class="flex shrink-0 items-center gap-2 rounded-xl border px-3 py-2" style="border-color:var(--border-color);">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activas</span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" id="estadoToggle" class="sr-only peer" {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                        <span class="h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700"></span>
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
                    </label>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
            <div class="overflow-x-auto"><table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-center">Nombre</th>
                        <th class="px-6 py-4 text-center">Descripción</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marcas as $marca)
                        <x-table-row :id="$marca->id" data-id="{{ $marca->id }}">
                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">
                                {{ ($marcas->currentPage() - 1) * $marcas->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold" style="color:var(--text-main);">{{ $marca->nombre }}</td>
                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ $marca->descripcion ?: 'Sin descripción' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if ($marca->estado)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                @endif
                            </td>
                            <x-table-actions
                                :id="$marca->id"
                                baseUrl="admin/transporte/maestros/bus_marcas"
                                :show="false"
                                :toggle="false"
                                :on-edit="'abrirModalEditarMarca(' . json_encode($marca) . ')'"
                            >
                                @if ($marca->estado)
                                    <form action="{{ route('admin.transporte.maestros.bus_marcas.destroy', $marca) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="event.stopPropagation(); confirmAccion(event, this, 'inactivar', 'marca')"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50" title="Inactivar">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.transporte.maestros.bus_marcas.activar', $marca) }}" method="POST" class="inline">
                                        @csrf @method('PUT')
                                        <button type="submit" onclick="event.stopPropagation(); confirmAccion(event, this, 'activar', 'marca')"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/50" title="Activar">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </x-table-actions>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay marcas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>

            <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">
                {{ $marcas->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
        </div>

    {{-- ==================== MODAL CREAR ==================== --}} 
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border shadow-2xl" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color:var(--border-color);">
                    <h5 class="text-lg font-black" style="color:var(--text-main);" id="modalCrearLabel">
                        <i class="fas fa-plus-circle mr-2" style="color:var(--color-primary)"></i>Nueva Marca
                    </h5>
                    <button type="button" onclick="closeModal('modalCrear')" class="text-gray-400 hover:text-red-600">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formCrear" action="{{ route('admin.transporte.maestros.bus_marcas.store') }}" method="POST" class="rd-prevent-double-submit">
                    @csrf
                    <div class="space-y-4 p-5">
                        <div>
                            <label class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre</label>
                            <div class="flex items-center rounded-xl border" style="background-color:var(--input-bg);border-color:var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-tag"></i></span>
                                <input type="text" name="nombre"
                                    class="w-full border-0 bg-transparent px-3 py-2.5 text-sm outline-none focus:ring-0 @error('nombre') border-red-300 @enderror" style="color:var(--text-main);"
                                    placeholder="Ej: Toyota" value="{{ old('nombre') }}" maxlength="100" autofocus>
                            </div>
                            @error('nombre')
                                <div class="mt-1 text-sm font-bold text-red-600"><b>{{ $message }}</b></div>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold" style="color:var(--text-main);">DescripciÃ³n <span class="text-xs text-gray-400">(opcional)</span></label>
                            <div class="flex items-center rounded-xl border" style="background-color:var(--input-bg);border-color:var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-align-left"></i></span>
                                <input type="text" name="descripcion"
                                    class="w-full border-0 bg-transparent px-3 py-2.5 text-sm outline-none focus:ring-0" style="color:var(--text-main);"
                                    placeholder="Ej: Marca japonesa de vehÃ­culos"
                                    value="{{ old('descripcion') }}" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t p-5" style="border-color:var(--border-color);">
                        <button type="button" onclick="closeModal('modalCrear')" class="rounded-xl border px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">Cancelar</button>
                        <button type="submit" class="rd-submit-btn rounded-xl bg-red-800 px-4 py-2 text-sm font-bold text-white hover:bg-red-900">
                            <i class="fas fa-check"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
    </div>

    {{-- ==================== MODAL EDITAR ==================== --}}
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border shadow-2xl" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color:var(--border-color);">
                    <h5 class="text-lg font-black" style="color:var(--text-main);" id="modalEditarLabel">
                        <i class="fas fa-edit mr-2" style="color:var(--color-primary)"></i>Editar Marca
                    </h5>
                    <button type="button" onclick="closeModal('modalEditar')" class="text-gray-400 hover:text-red-600">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formEditar" action="" method="POST" class="rd-prevent-double-submit">
                    @csrf @method('PUT')
                    <div class="space-y-4 p-5">
                        <div>
                            <label class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre</label>
                            <div class="flex items-center rounded-xl border" style="background-color:var(--input-bg);border-color:var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-tag"></i></span>
                                <input type="text" id="editNombre" name="nombre"
                                    class="w-full border-0 bg-transparent px-3 py-2.5 text-sm outline-none focus:ring-0" style="color:var(--text-main);"
                                    placeholder="Nombre de la marca" maxlength="100">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold" style="color:var(--text-main);">DescripciÃ³n <span class="text-xs text-gray-400">(opcional)</span></label>
                            <div class="flex items-center rounded-xl border" style="background-color:var(--input-bg);border-color:var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-align-left"></i></span>
                                <input type="text" id="editDescripcion" name="descripcion"
                                    class="w-full border-0 bg-transparent px-3 py-2.5 text-sm outline-none focus:ring-0" style="color:var(--text-main);"
                                    placeholder="DescripciÃ³n de la marca" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t p-5" style="border-color:var(--border-color);">
                        <button type="button" onclick="closeModal('modalEditar')" class="rounded-xl border px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">Cancelar</button>
                        <button type="submit" class="rd-submit-btn rounded-xl bg-red-800 px-4 py-2 text-sm font-bold text-white hover:bg-red-900">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@push('js')
    <script>
    function abrirModalEditarMarca(marca) {
        document.getElementById('editNombre').value = marca.nombre ?? '';
        document.getElementById('editDescripcion').value = marca.descripcion === 'Ninguna' ? '' : (marca.descripcion ?? '');
        document.getElementById('formEditar').action = `/admin/transporte/maestros/bus_marcas/${marca.id}`;
        document.getElementById('formEditar').dataset.id = marca.id;
        openModal('modalEditar');
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Utilidad: mostrar toast de Ã©xito Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    function toastExito(mensaje) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: mensaje,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Utilidad: mostrar errores de validaciÃ³n dentro del modal Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    function mostrarErrores(errors, formId) {
        // Limpia errores anteriores
        document.querySelectorAll(`${formId} .text-danger`).forEach(e => e.remove());
        document.querySelectorAll(`${formId} .border-red-300`).forEach(e => e.classList.remove('border-red-300'));

        Object.keys(errors).forEach(function(campo) {
            const input = document.querySelector(`${formId} [name="${campo}"]`);
            if (input) {
                input.classList.add('border-red-300');
                const div = document.createElement('div');
                div.className = 'text-danger mt-1';
                div.innerHTML = `<b>${errors[campo][0]}</b>`;
                input.closest('.flex items-center rounded-xl border border-slate-200 bg-slate-50').after(div);
            }
        });
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ CREAR Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    document.getElementById('formCrear').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        const btnSubmit = form.querySelector('.rd-submit-btn');
        const originalHtml = btnSubmit.innerHTML;

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: data,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                $('#modalCrear').modal('hide');
                form.reset();
                // Agregar fila a la tabla
                agregarFilaTabla(res.marca);
                toastExito(res.message);
            } else {
                mostrarErrores(res.errors ?? {}, '#formCrear');
            }
        })
        .catch(() => mostrarErrores({ nombre: ['Error inesperado, intente de nuevo.'] }, '#formCrear'))
        .finally(() => {
            // Restaurar estado del botÃ³n
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;
        });
    });

    // Ã¢â€â‚¬Ã¢â€â‚¬ EDITAR Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    document.querySelectorAll('.btn-abrir-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editNombre').value       = this.dataset.nombre;
            document.getElementById('editDescripcion').value  = this.dataset.descripcion === 'Ninguna' ? '' : this.dataset.descripcion;
            document.getElementById('formEditar').action      = `/admin/transporte/maestros/bus_marcas/${this.dataset.id}`;
            document.getElementById('formEditar').dataset.id  = this.dataset.id;
        });
    });

    document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        data.append('_method', 'PUT');
        const btnSubmit = form.querySelector('.rd-submit-btn');
        const originalHtml = btnSubmit.innerHTML;

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: data,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                $('#modalEditar').modal('hide');
                // Actualizar fila existente
                actualizarFilaTabla(res.marca);
                toastExito(res.message);
            } else {
                mostrarErrores(res.errors ?? {}, '#formEditar');
            }
        })
        .catch(() => mostrarErrores({ nombre: ['Error inesperado, intente de nuevo.'] }, '#formEditar'))
        .finally(() => {
            // Restaurar estado del botÃ³n
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;
        });
    });

    // Ã¢â€â‚¬Ã¢â€â‚¬ INACTIVAR / ACTIVAR Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    function confirmAccion(event, button, accion, entidad) {
        event.preventDefault();
        Swal.fire({
            title: 'Â¿EstÃ¡s seguro?',
            text: `Â¿Desea ${accion} la ${entidad}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `SÃ­, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const form   = button.closest('form');
            const url    = form.action;
            const method = form.querySelector('[name="_method"]')?.value ?? 'POST';

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: new FormData(form),
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Recargar solo la fila cambiando el badge y los botones
                    const fila = button.closest('tr');
                    fila.remove(); // mÃ¡s simple: si filtra por estado activo/inactivo, la fila desaparece igual
                    toastExito(res.message);
                }
            });
        });
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Helpers DOM Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    let contadorFilas = {{ $marcas->total() }};

    function agregarFilaTabla(marca) {
        contadorFilas++;
        const tbody = document.querySelector('table tbody');
        // Quita el "No hay marcas" si existe
        const vacio = tbody.querySelector('td[colspan]');
        if (vacio) vacio.closest('tr').remove();

        const descripcion = marca.descripcion || 'Ninguna';
        const fila = `
            <tr data-id="${marca.id}" class="fila-tabla cursor-pointer transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]"
                onclick="toggleAccionesTabla(event, ${marca.id})">
                <td class="text-center">${contadorFilas}</td>
                <td class="text-center">${marca.nombre}</td>
                <td class="text-center">${descripcion}</td>
                <td class="whitespace-nowrap px-6 py-4 text-center">
                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <i class="fas fa-check-circle"></i> Activo
                    </span>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                    <div class="acciones-wrap relative flex h-8 items-center justify-center">
                        <div class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50">
                            <i class="fas fa-ellipsis-vertical text-xs"></i>
                        </div>
                        <div class="acciones-panel">
                            <button type="button" onclick="event.stopPropagation()" class="btn-abrir-editar flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50"
                            data-id="${marca.id}"
                            data-nombre="${marca.nombre}"
                            data-descripcion="${descripcion}"
                            title="Editar">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                        <form action="/admin/transporte/maestros/bus_marcas/${marca.id}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50"
                                onclick="event.stopPropagation(); confirmAccion(event, this, 'inactivar', 'marca')">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                        </div>
                    </div>
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', fila);
        // Re-bindear el nuevo botÃ³n de editar
        tbody.querySelector(`tr[data-id="${marca.id}"] .btn-abrir-editar`)
            .addEventListener('click', function() {
                document.getElementById('editNombre').value      = this.dataset.nombre;
                document.getElementById('editDescripcion').value = this.dataset.descripcion === 'Ninguna' ? '' : this.dataset.descripcion;
                document.getElementById('formEditar').action     = `/admin/transporte/maestros/bus_marcas/${this.dataset.id}`;
            });
    }

    function actualizarFilaTabla(marca) {
        const fila = document.querySelector(`tr[data-id="${marca.id}"]`);
        if (!fila) return;
        const descripcion = marca.descripcion || 'Ninguna';
        fila.cells[1].textContent = marca.nombre;
        fila.cells[2].textContent = descripcion;
        // Actualizar data-* del botÃ³n editar para la prÃ³xima vez
        const btnEditar = fila.querySelector('.btn-abrir-editar');
        if (btnEditar) {
            btnEditar.dataset.nombre      = marca.nombre;
            btnEditar.dataset.descripcion = descripcion;
        }
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Toggle estado Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    document.getElementById('estadoToggle').addEventListener('change', function() {
        const params = new URLSearchParams(window.location.search);
        params.set('estado', this.checked ? 1 : 0);
        window.location.href = "{{ route('admin.transporte.maestros.bus_marcas.index') }}?" + params.toString();
    });
    </script>
    @endpush
    </x-app-layout>
