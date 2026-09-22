<div class="rd-wrapper">
    <!-- Formulario de registro diario -->
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(280px,320px)_minmax(0,1fr)] gap-3 items-start">
        <!-- Left: Cedula buscador -->
        <div class="rd-card rd-card-search rounded-2xl border shadow-sm overflow-hidden" style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="rd-card-headerr border-b px-5 py-4" style="border-color: var(--border-color);">
                <h2 class="rd-title">Registro Diario</h2>
                <p class="rd-sub">Escanea el código de barras del carnet para registrar la entrada</p>
            </div>

            <div class="rd-card-body px-5 py-5">
                <form wire:submit.prevent="save" class="rd-search-form" autocomplete="off">
                    @csrf
                    <div style="display: flex;gap: 10px;align-items: center; justify-content: space-between;">
                        <label for="cedula" class="sr-only">Cédula</label>
                        <input type="tel" id="cedula" wire:model.defer="cedula" @disabled(!$receta_diario || !$enableInput)
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('cedula') rd-input-error @enderror" placeholder="Ej: 12345678"
                            maxlength="8" inputmode="numeric" autofocus @if (!$enableInput)
                                style="cursor: not-allowed;"
                            @endif/>

                        <button class="rd-btn rd-btn-primary inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900" type="submit" @disabled(!$enableInput)
                         aria-label="Buscar"  @if(!$enableInput) style="opacity: .8; cursor: not-allowed;" @endif>Buscar</button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Solo números, máximo 8 dígitos. <br />
                        También puedes escribir el número manualmente si es necesario.
                    </small>

                    @error('cedula')
                        <div class="rd-error mt-2">{{ $message }}</div>
                    @enderror
                </form>

                <!-- Notificación como toast (Livewire controla showNotification) -->
                <div class="rd-toast-holder">
                    @if ($showNotification && isset($notification['message']))
                        <div class="rd-toast rd-toast-{{ $notification['type'] ?? 'info' }}" role="status"
                            aria-live="polite">
                            <div class="rd-toast-body">
                                @php
                                    if ($notification['type'] == 'success') {
                                        $type = 'exito';
                                    } else {
                                        $type = 'error';
                                    }
                                @endphp
                                <strong>{{ ucfirst($type) }}</strong>
                                <span>{{ $notification['message'] }}</span>
                            </div>
                            <button class="rd-toast-close" aria-label="Cerrar"
                                wire:click="$set('showNotification', false)">×</button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rd-card-footer text-center">
                <small class="text-muted">Mantén tu carnet a mano</small>
            </div>
        </div>

        <!-- Right: Buscador, filtros y tabla -->
        <div class="rd-card rd-card-list rounded-2xl border shadow-sm overflow-hidden" style="height: 100%; background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="rd-card-header rd-header-space" style="display:flex; flex-direction:column; align-items:stretch; gap:16px;">
                <div class="shrink-0">
                    <h3 class="rd-title-sm">Registros</h3>
                    <p class="rd-sub-sm">Últimos movimientos del día</p>
                </div>

                <div class="flex items-center justify-between gap-6">
                    <div class="rd-actions shrink-0" style="display:flex; align-items:center; gap:8px; min-width:0;">
                    <form action="{{ route('admin.movimientos.registro_diario.index') }}" method="GET"
                        class="rd-search-inline flex items-center gap-2 shrink-0" style="flex-wrap:nowrap;" role="search">
                        <input name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input h-10 w-56"
                            placeholder="Nombre, apellido o PNF" id="search" />
                        <button class="rd-btn rd-btn-primary inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-900" type="submit" title="Buscar">
                            <i class="fas fa-search text-xs"></i>
                            <span>Buscar</span>
                        </button>
                    </form>

                    <button class="rd-icon-btn h-10 w-10 shrink-0"   aria-expanded="false"
                        aria-controls="filters" title="Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                    </div>

                    <div class="flex shrink-0 items-center justify-end gap-2">
                    @if ($showBtnFinalizar)
                        <button class="rd-btn rd-btn-alter order-3 inline-flex h-10 min-w-[136px] items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-900" title="Finalizar Día" id="finalizarDia">
                            <i class="fas fa-sun"></i>
                            Finalizar Dia
                        </button>  
                    @endif
                    <!-- Modal Finalizar Dia -->
                    <div wire:ignore.self class="hidden rd-modal-overlay" id="modalFinalizarDia" tabindex="-1" aria-hidden="true">
                        <div class="rd-modal-dialog">
                            <div class="modal-content rd-card rd-modal-content border-0">
                                <div class="modal-header border-bottom-0 pt-4 px-4">
                                    <h5 class="rd-title-sm" style="font-size: 1.25rem;">
                                        <i class="fas fa-file-signature me-2" style="color: var(--color-tertiary);"></i>
                                        Reporte de Cierre de Jornada
                                    </h5>
                                </div>
                                
                                <form wire:submit.prevent="finalizarDia" id="formFinalizarDia">
                                    <div class="modal-body px-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="rd-label mb-2">Fecha de Cierre</label>
                                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-light">
                                                    <span><i class="fas fa-calendar-day"></i></span>
                                                    <input wire:model="fecha" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" id="fechaCierre" readonly >
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="rd-label mb-2">Cantidad Sobrante</label>
                                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                                    <span><i class="fas fa-utensils"></i></span>
                                                    <input wire:model="sobrante" type="number" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" id="cantidadSobrante" placeholder="0" min="0" required readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="rd-label mb-2">Motivo del Cierre</label>
                                            <select wire:model="motivo" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input w-100" id="motivoCierre" >
                                                <option value="">Seleccione el motivo...</option>

                                                <option value="Baja personal">Baja asistencia del personal operativo</option>
                                                <option value="Suspension actividades">Suspensión de actividades académicas (paros, asambleas, elecciones)</option>
                                                <option value="Horario reducido">Reducción de jornada académica</option>
                                                <option value="Baja asistencia estudiantil">Baja asistencia estudiantil no prevista</option>
                                                <option value="Cambio horario estudiantes">Cambio inesperado en horarios académicos</option>
                                                <option value="Sobreestimacion demanda">Sobreestimación de la demanda diaria</option>
                                                <option value="Entrega tardia">Entrega tardía de alimentos preparados</option>
                                                <option value="Emergencia">Emergencia o contingencia (climática, sanitaria, seguridad)</option>
                                                <option value="Otro">Otro (especificar en observaciones)</option>
                                            </select>
                                            @error('motivo')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="rd-label mb-2">Acción Tomada con el Sobrante</label>
                                            <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                                <span><i class="fas fa-hand-holding-heart"></i></span>
                                                <input wire:model="accion" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" id="accionTomada" placeholder="Ej: Donación, refrigeración, descarte..." >
                                            </div>
                                            @error('accion')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top-0 pb-4 px-4 gap-2">
                                        <button type="button" id="cancelarFinalizarDia"
                                            class="rd-btn inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-red-700 px-4 py-2.5 text-sm font-extrabold text-red-400 transition hover:bg-red-950/50">
                                            Cancelar
                                        </button>
                                        <button type="submit" id="btnConfirmarCierre"
                                            class="rd-btn inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-900">
                                            <i class="fas fa-save text-xs"></i> Guardar y Finalizar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    

                    <div class="rd-export-group order-1 flex shrink-0 items-center gap-2" style="display:flex; flex-direction:row; align-items:center; gap:8px;">
                        <a href="{{ route('admin.movimientos.registro_diario.export_excel', request()->only(['buscar', 'fecha_desde', 'fecha_hasta'])) }}"
                            class="rd-btn rd-btn-alter inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-full border border-emerald-700 bg-[#071b16] px-4 py-2.5 text-sm font-extrabold shadow-sm transition hover:bg-[#0a241d]"
                            style="color: #34d399 !important;" title="Exportar Excel"><i class="fas fa-file-excel"
                                style="color: #34d399 !important;"></i>
                            Excel</a>

                        <a href="{{ route('admin.movimientos.registro_diario.export_pdf', request()->only(['buscar', 'fecha_desde', 'fecha_hasta'])) }}"
                            class="rd-btn rd-btn-alter inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-full border border-red-700 bg-[#22090d] px-4 py-2.5 text-sm font-extrabold shadow-sm transition hover:bg-[#2d0b11]"
                            style="color: #f87171 !important;" title="Exportar PDF"><i class="fas fa-file-pdf"
                                style="color: #f87171 !important;"></i>
                            PDF</a>
                    </div>
                    <span class="order-2 mx-2 h-10 w-px bg-red-900/60" aria-hidden="true"></span>
                    </div>
                </div> 
            </div>

            <div class="collapse" id="filters">
                <div class="rd-filters rounded-2xl border p-3 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <form action="{{ route('admin.movimientos.registro_diario.index') }}" method="GET"
                        class="rd-filters-form">
                        <div class="rd-filter-row">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" max="{{ date('Y-m-d') }}"value="{{ request("fecha_desde") }}"/>
                        </div>
                        <div class="rd-filter-row">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                max="{{ date('Y-m-d') }}" value="{{ request("fecha_hasta") }}"/>
                        </div>
                        <div class="rd-filter-actions">
                            <button class="rd-btn rd-btn-primary" type="submit">Aplicar</button>
                            <button type="button" class="rd-btn rd-btn-default"
                                onclick="window.location='{{ route('admin.movimientos.registro_diario.index') }}'">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rd-card-body rd-list-body p-0">
                <div class="rd-list">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width: 80px;">#</th>
                                <th class="px-6 py-4 text-center">Nombre</th>
                                <th class="px-6 py-4 text-center">Apellido</th>
                                <th class="px-6 py-4 text-center">PNF</th>
                                <th class="px-6 py-4 text-center">Registrado</th>
                                <th class="px-6 py-4 text-center" style="width: 140px;">Estado</th>
                                <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-medium">
                            @forelse ($data as $registro)
                                <x-table-row :id="$registro->id" class="border-b" style="border-color: var(--border-color);">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color: var(--text-main);">
                                        {{ $registro->nombre_persona }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap" style="color: var(--text-main);">
                                        {{ $registro->apellido_persona }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $registro->nombre_pnf }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-[#071b16] border border-emerald-700 shadow-sm"
                                            style="color: #34d399 !important;">
                                            <i class="fas fa-check-circle" style="color: #34d399 !important;"></i> Aprobado
                                        </span>
                                    </td>
                                    <x-table-actions :id="$registro->id" :show="false" :edit="false" :toggle="false">
                                        <a href="{{ route('admin.movimientos.registro_diario.show', $registro->id) }}"
                                            onclick="event.stopPropagation()"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                                            title="Ver detalle">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </x-table-actions>
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        No hay registros
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                    {{ $data->onEachSide(1)->links('components.pagination-livewire') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal', data => {
                console.log(data);
                Swal.fire({
                    title: data[0].title,
                    text: data[0].text,
                    icon: data[0].icon,
                    confirmButtonText: 'Aceptar',
                    timer: 5000,
                    timerProgressBar: true,
                });
            });
        });
        document.addEventListener('livewire:initialized', () => {
            let isNotificationVisible = false;
            let hideTimeout = null;

            @this.on('notify-saved', () => {
                // Si ya hay una notificación visible, no hacer nada
                if (isNotificationVisible) {
                    return;
                }

                isNotificationVisible = true;

                // Ocultar después de 3 segundos
                hideTimeout = setTimeout(() => {
                    @this.set('showNotification', false);
                    isNotificationVisible = false;
                }, 3000);
            });

            // Limpiar el estado cuando la notificación se oculta
            @this.on('notify-hidden', () => {
                isNotificationVisible = false;
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                }
            });

            Livewire.on('notify-inventario', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Inventario insuficiente',
                    text: @this.get('alertInventario'),
                });
            });

            Livewire.on('notify-limite', () => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: @this.get('alertLimite'),
                });
            });
        });

        // Evento para el input de cédula
        const inputCedula = document.getElementById('cedula');
        const inputSearch = document.getElementById('search');

        //No cargar el script si esta en blur
        const bgBlur = document.querySelector(".rd-blur");
        if (!bgBlur) {
            if (inputCedula) {
                let blockCedulaFocus = false;
    
                const focusCedulaSafely = () => {
                    const isModalOpen = document.querySelector('#modalFinalizarDia.show');
                    if (blockCedulaFocus || isModalOpen) return;
                    inputCedula.focus({
                        preventScroll: true
                    });
                };
    
                // Focus inicial
                focusCedulaSafely();
    
                if (inputSearch) {
                    inputSearch.addEventListener('focus', () => {
                        blockCedulaFocus = true;
                    });
                    inputSearch.addEventListener('blur', () => {
                        blockCedulaFocus = false;
                        focusCedulaSafely();
                    });
                }
    
                // Escuchar click en el contenedor principal
                const root = document.querySelector('.content-wrapper') || document.body;
                root.addEventListener('click', (event) => {
                    if (inputSearch && (event.target === inputSearch || inputSearch.contains(event.target))) {
                        return;
                    }
                    focusCedulaSafely();
                });
    
                // Re-enfocar cuando la ventana retorne, respetando el focus del search
                window.addEventListener('focus', () => {
                    focusCedulaSafely();
                });
                //Limites del input
                inputCedula.addEventListener('input', function(e) {
                    // Remover caracteres no numéricos
                    this.value = this.value.replace(/[^0-9]/g, '');
        
                    // Limitar a 8 dígitos máximo
                    if (this.value.length > 8) {
                        this.value = this.value.slice(0, 8);
                    }
                });
            }
        }


        const finalizarModal = document.getElementById('modalFinalizarDia');
        document.addEventListener('DOMContentLoaded', () => {
            //Script para el boton de finalizarDia
            const finalizarBtn = document.querySelector('#finalizarDia')
            if (!finalizarBtn || !finalizarModal) return;

            finalizarBtn.addEventListener('click', function() {
                //Mostramos una alerta de confirmacion
                Swal.fire({
                    title: '¿Estas seguro de finalizar el dia?',
                    icon: 'warning',
                    text: 'Al finalizar el dia, no podras registrar mas estudiantes',
                    showCancelButton: true,
                    confirmButtonText: 'Si, finalizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        //emitimos el evento
                        @this.openModal();
                    }
                });
            });

            const cerrarModal = () => {
                finalizarModal.classList.add('hidden');
                finalizarModal.setAttribute('aria-hidden', 'true');
            };

            document.querySelector('#cancelarFinalizarDia')?.addEventListener('click', cerrarModal);

            // Escuchamos el evento que viene del servidor (PHP)
            Livewire.on('openModal', () => {
                finalizarModal.classList.remove('hidden');
                finalizarModal.setAttribute('aria-hidden', 'false');
            });
            
            Livewire.on('finalizar-dia-guardado', (event) => {
                const message = event[0] ?? event;
                cerrarModal();
                Swal.fire({
                    icon: message.icon,
                    title: message.title,
                    text: message.text,
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        });



    </script>
@endpush
