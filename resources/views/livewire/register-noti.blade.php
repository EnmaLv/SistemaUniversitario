<div class="rd-wrapper">
    @include('components.alert')
    <!-- Formulario de registro diario -->
    <div class="rd-grid">
        <!-- Left: Cedula buscador -->
        <div class="rd-card rd-card-search">
            <div class="rd-card-headerr">
                <h2 class="rd-title">Registro Diario</h2>
                <p class="rd-sub">Escanea el código de barras del carnet para registrar la entrada</p>
            </div>

            <div class="rd-card-body">
                <form wire:submit.prevent="save" class="rd-search-form" autocomplete="off">
                    @csrf
                    <div style="display: flex;gap: 10px;align-items: center; justify-content: space-between;">
                        <label for="cedula" class="sr-only">Cédula</label>
                        <input type="tel" id="cedula" wire:model.defer="cedula" @disabled(!$receta_diario || !$enableInput)
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('cedula') rd-input-error @enderror" placeholder="Ej: 12345678"
                            maxlength="8" inputmode="numeric" autofocus @if (!$enableInput)
                                style="cursor: not-allowed;"
                            @endif/>

                        <button class="rd-btn rd-btn-primary" type="submit" @disabled(!$enableInput)
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
        <div class="rd-card rd-card-list" style="height: 100%">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Registros</h3>
                    <p class="rd-sub-sm">Últimos movimientos del día</p>
                </div>

                <div class="rd-actions">
                    <form action="{{ route('admin.movimientos.registro_diario.index') }}" method="GET"
                        class="rd-search-inline" role="search">
                        <input name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input"
                            placeholder="Nombre, apellido o PNF" id="search" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>

                    <button class="rd-icon-btn"   aria-expanded="false"
                        aria-controls="filters" title="Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if ($showBtnFinalizar)
                        <button class="rd-btn rd-btn-alter" title="Finalizar Dia" id="finalizarDia">
                            <i class="fas fa-sun"></i>
                            Finalizar Dia
                        </button>  
                    @endif
                    <!-- Modal Finalizar Dia -->
                    <div wire:ignore.self class="hidden" id="modalFinalizarDia" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-md"> <div class="modal-content rd-card border-0">
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
                                        <button type="button" class="rd-btn rd-btn-default" >Cancelar</button>
                                        <button type="submit" class="rd-btn rd-btn-primary" id="btnConfirmarCierre">
                                            <i class="fas fa-save me-1"></i> Guardar y Finalizar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    

                    <div class="rd-export-group">
                        <a href="{{ route('admin.movimientos.registro_diario.export_excel', request()->only(['buscar', 'fecha_desde', 'fecha_hasta'])) }}"
                            class="rd-btn rd-btn-success" title="Exportar Excel"><i class="fas fa-file-excel"></i>
                            Excel</a>

                        <button class="rd-btn rd-btn-danger" title="Exportar PDF" id="pdfBtn"><i
                                class="fas fa-file-pdf"></i>
                            PDF</button>
                    </div>
                </div> 
            </div>

            <div class="collapse" id="filters">
                <div class="rd-filters">
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

            <div class="rd-card-body rd-list-body">
                <div class="rd-list">
                    <table class="rd-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>PNF</th>
                                <th>Registrado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $registro)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $registro->nombre_persona }}</td>
                                    <td>{{ $registro->apellido_persona }}</td>
                                    <td>{{ $registro->nombre_pnf }}</td>
                                    <td>{{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="rd-badge rd-badge-success">Aprobado</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="rd-action-group">
                                            <a class="rd-action"
                                                href="{{ route('admin.movimientos.registro_diario.show', $registro->id) }}"
                                                title="Ver"><i class="fas fa-eye"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-center">
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
        document.addEventListener('DOMContentLoaded', ()=>{
            //Script para el boton de finalizarDia
            const finalizarBtn = document.querySelector('#finalizarDia')
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
            })
        })


        document.querySelector('button[]').addEventListener('click', function() {
            finalizarModal.hide();
        }); 

        // Escuchamos el evento que viene del servidor (PHP)
        document.addEventListener('livewire:initialized', () => {
            @this.on('openModal', () => {
                // Mostramos la modal de forma segura una vez el DOM está listo
                        finalizarModal.classList.remove('hidden');
            });
            
            @this.on('finalizar-dia-guardado', (event) => {
                let message = event[0]
                finalizarModal.hide();
                Swal.fire({
                    icon: message.icon,
                    title: message.title,
                    text: message.text,
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        });



        //Script para mostrar el PdfGeneratorUtil
        const pdfBtn = document.querySelector('#pdfBtn');
        const pdfRoute = `{{ route('admin.movimientos.registro_diario.export_pdf') }}`;
        if (pdfBtn) {
            pdfBtn.addEventListener('click', function() {

                const params = new URLSearchParams(window.location.search);
                const fechaDesde = params.get('fecha_desde') ?? "";
                const fechaHasta = params.get('fecha_hasta') ?? "";

                const url = `${pdfRoute}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;
                window.open(url, '_blank');
            });
        }
    </script>
@endpush
