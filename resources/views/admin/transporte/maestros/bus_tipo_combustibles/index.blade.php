@extends('layouts.app')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Tipos de Combustible</h1>
                <p class="mt-1 text-sm text-gray-500">Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.</p>
            </div>
            <button type="button" onclick="openModal('modalCrear')" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white transition-colors hover:bg-red-900">
                <i class="fas fa-plus"></i> Nuevo Tipo
            </button>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black" style="color:var(--text-main);">Tipos de Combustible Registrados</h3>
                </div>
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-500">Activos</span>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="estadoToggle" class="peer sr-only" {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <span class="h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700"></span>
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
                        </label>
                    </div>
                    <form action="{{ route('admin.transporte.maestros.bus_tipo_combustibles.index') }}" method="GET" class="relative w-full md:w-64" role="search">
                        <input type="hidden" name="estado" value="{{ request('estado', 1) }}">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="w-full rounded-xl border py-2.5 pl-4 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" style="background-color:var(--input-bg);border-color:var(--border-color);color:var(--text-main);" placeholder="Buscar tipo..." />
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-center">Nombre</th>
                        <th class="px-6 py-4 text-center">Descripción</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-xs font-medium">
                    @forelse($tipos as $tipo)
                        <x-table-row :id="$tipo->id" data-id="{{ $tipo->id }}">
                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ ($tipos->currentPage() - 1) * $tipos->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 text-center font-bold" style="color:var(--text-main);">{{ $tipo->nombre }}</td>
                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ $tipo->descripcion }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if ($tipo->estado)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400"><i class="fas fa-check-circle"></i> Activo</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400"><i class="fas fa-times-circle"></i> Inactivo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="acciones-wrap relative flex h-8 items-center justify-center">
                                    <div class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50"><i class="fas fa-ellipsis-vertical text-xs"></i></div>
                                    <div class="acciones-panel">
                                    <button type="button" class="btn-abrir-editar flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50" data-id="{{ $tipo->id }}" data-nombre="{{ $tipo->nombre }}" data-descripcion="{{ $tipo->descripcion }}" title="Editar" onclick="event.stopPropagation(); openEditarTipo(this)">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    @if ($tipo->estado)
                                        <form action="{{ route('admin.transporte.maestros.bus_tipo_combustibles.destroy', $tipo) }}" method="POST" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50" onclick="event.stopPropagation(); confirmAccion(event, this, 'inactivar', 'tipo')" title="Inactivar"><i class="fas fa-trash-alt text-xs"></i></button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.transporte.maestros.bus_tipo_combustibles.activar', $tipo) }}" method="POST" class="inline-block">
                                            @csrf @method('PUT')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/50" onclick="event.stopPropagation(); confirmAccion(event, this, 'activar', 'tipo')" title="Activar"><i class="fas fa-check text-xs"></i></button>
                                        </form>
                                    @endif
                                    </div>
                                </div>
                            </td>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay tipos de combustible registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">
            {{ $tipos->onEachSide(1)->links('components.pagination') }}
        </div>
    </div>

    <div id="modalCrear" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h5 class="text-lg font-semibold text-slate-900">Nuevo Tipo de Combustible</h5>
                <button type="button" class="text-slate-500 hover:text-slate-700" onclick="closeModal('modalCrear')"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formCrear" action="{{ route('admin.transporte.maestros.bus_tipo_combustibles.store') }}" method="POST" class="space-y-4 p-5">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-gas-pump"></i></span>
                        <input type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Ej: Gasolina" maxlength="100" autofocus>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Descripción <span class="text-xs text-slate-400">(opcional)</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-align-left"></i></span>
                        <input type="text" name="descripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Ej: Combustible de 95 octanos" maxlength="255">
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" onclick="closeModal('modalCrear')">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h5 class="text-lg font-semibold text-slate-900">Editar Tipo de Combustible</h5>
                <button type="button" class="text-slate-500 hover:text-slate-700" onclick="closeModal('modalEditar')"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formEditar" action="" method="POST" class="space-y-4 p-5">
                @csrf @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-gas-pump"></i></span>
                        <input type="text" id="editNombre" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Nombre del tipo" maxlength="100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Descripción <span class="text-xs text-slate-400">(opcional)</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-align-left"></i></span>
                        <input type="text" id="editDescripcion" name="descripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Descripción del tipo" maxlength="255">
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditarTipo(button) {
            document.getElementById('editNombre').value = button.dataset.nombre || '';
            document.getElementById('editDescripcion').value = button.dataset.descripcion || '';
            document.getElementById('formEditar').action = `/admin/transporte/maestros/bus_tipo_combustibles/${button.dataset.id}`;
            openModal('modalEditar');
        }

        function toastExito(mensaje) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: mensaje, showConfirmButton: false, timer: 3000, timerProgressBar: true });
        }

        function mostrarErrores(errors, formId) {
            document.querySelectorAll(`${formId} .text-red-600`).forEach(e => e.remove());
            document.querySelectorAll(`${formId} .border-red-300`).forEach(e => e.classList.remove('border-red-300'));
            Object.keys(errors).forEach(function(campo) {
                const input = document.querySelector(`${formId} [name="${campo}"]`);
                if (input) {
                    input.classList.add('border-red-300');
                    const div = document.createElement('div');
                    div.className = 'mt-1 text-sm font-medium text-red-600';
                    div.innerHTML = `<b>${errors[campo][0]}</b>`;
                    input.closest('div').after(div);
                }
            });
        }

        document.getElementById('formCrear').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: data,
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModal('modalCrear');
                    form.reset();
                    toastExito(res.message);
                    window.location.reload();
                } else {
                    mostrarErrores(res.errors ?? {}, '#formCrear');
                }
            })
            .catch(() => mostrarErrores({ nombre: ['Error inesperado, intente de nuevo.'] }, '#formCrear'));
        });

        document.getElementById('formEditar').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const data = new FormData(form);
            data.append('_method', 'PUT');
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: data,
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
        text: `Â¿Desea ${accion} el ${entidad}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `SÃ­, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = button.closest('form');
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: new FormData(form),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                button.closest('tr').remove();
                toastExito(res.message);
            }
        });
    });
}

// â”€â”€ Helpers DOM â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let contadorFilas = {{ $tipos->total() }};

function agregarFilaTabla(tipo) {
    contadorFilas++;
    const tbody = document.querySelector('table tbody');
    const vacio = tbody.querySelector('td[colspan]');
    if (vacio) vacio.closest('tr').remove();

    const descripcion = tipo.descripcion || 'Ninguna';
    const fila = `
        <tr data-id="${tipo.id}" class="fila-tabla cursor-pointer transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]" onclick="toggleAccionesTabla(event, ${tipo.id})">
            <td class="text-center">${contadorFilas}</td>
            <td class="text-center">${tipo.nombre}</td>
            <td class="text-center">${descripcion}</td>
            <td class="whitespace-nowrap px-6 py-4 text-center"><span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400"><i class="fas fa-check-circle"></i> Activo</span></td>
            <td class="whitespace-nowrap px-6 py-4">
                <div class="acciones-wrap relative flex h-8 items-center justify-center">
                    <div class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50"><i class="fas fa-ellipsis-vertical text-xs"></i></div>
                    <div class="acciones-panel">
                    <button type="button" onclick="event.stopPropagation()" class="btn-abrir-editar flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50"
                        data-id="${tipo.id}"
                        data-nombre="${tipo.nombre}"
                        data-descripcion="${descripcion}"

                        title="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <form action="${BASE_URL}/${tipo.id}" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="${CSRF}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50"
                            onclick="event.stopPropagation(); confirmAccion(event, this, 'inactivar', 'tipo')" title="Inactivar">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                    </div>
                </div>
            </td>
        </tr>`;
    tbody.insertAdjacentHTML('beforeend', fila);
    bindEditar(tbody.querySelector(`tr[data-id="${tipo.id}"] .btn-abrir-editar`));
}

function actualizarFilaTabla(tipo) {
    const fila = document.querySelector(`tr[data-id="${tipo.id}"]`);
    if (!fila) return;
    const descripcion = tipo.descripcion || 'Ninguna';
    fila.cells[1].textContent = tipo.nombre;
    fila.cells[2].textContent = descripcion;
    const btn = fila.querySelector('.btn-abrir-editar');
    if (btn) {
        btn.dataset.nombre      = tipo.nombre;
        btn.dataset.descripcion = descripcion;
    }
}

// â”€â”€ Toggle estado â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.getElementById('estadoToggle').addEventListener('change', function() {
    const params = new URLSearchParams(window.location.search);
    params.set('estado', this.checked ? 1 : 0);
    window.location.href = "{{ route('admin.transporte.maestros.bus_tipo_combustibles.index') }}?" + params.toString();
});
</script>
@endpush
