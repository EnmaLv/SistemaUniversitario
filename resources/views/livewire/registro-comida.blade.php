 <div class="rd-wrapper">
    @include('components.alert')
    <div class="rd-card rd-card-desayuno mb-4 col-md-12 text-center mx-auto">
        <div class="rd-card-header">
            <h2 class="rd-title">Desayuno del día</h2>
            <p class="rd-sub">Selecciona el desayuno de hoy y registra la cantidad servida</p>
        </div>
        <div class="rd-card-body">
            <form wire:submit.prevent="saveDesayuno" class="rd-search-form" autocomplete="off">
                @csrf
                <div class="row g-3">
                    @foreach ($desayunos_agregados as $index => $desayuno)
                        <div class="col-12 fade-in">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <select
                                            wire:model.live="desayunos_agregados.{{ $index }}.receta_id"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('desayunos_agregados.' . $index . '.receta_id') rd-input-error @enderror"
                                            @disabled($desayuno_registrado || !$horarioPermitido) @if ($desayuno_registrado || !$horarioPermitido) style="opacity: .8; cursor: not-allowed;" @endif>
                                            <option value="">Seleccione una opción</option>
                                            @foreach ($comidas as $comida)
                                                <option value="{{ $comida->id }}">{{ $comida->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group mr-2">
                                            <input type="number"
                                                wire:model.live="desayunos_agregados.{{ $index }}.cantidad"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('desayunos_agregados.' . $index . '.cantidad') rd-input-error @enderror"
                                                placeholder="Cant." min="1" @disabled($desayuno_registrado || !$horarioPermitido) @if ($desayuno_registrado || !$horarioPermitido) style="opacity: .8; cursor: not-allowed;" @endif/>
                                        </div>
                                        {{-- Botón de Eliminar (Pequeño, solo visible si hay más de una entrada) --}}
                                        @if(count($desayunos_agregados) > 1 && !$desayuno_registrado)
                                            <button type="button" wire:click="removeDesayuno({{ $index }})" 
                                                class="rd-btn rd-btn-eliminar ml-auto"> 
                                                Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="col-12"><hr class="my-3"></div> 
                        @endif

                    @endforeach
                    <div class="col-md-12 d-flex mt-4 justify-content-end align-items-center">
                        <button type="button" wire:click="addDesayuno" style="@if ($desayuno_registrado || !$horarioPermitido) opacity: 0.5; cursor: not-allowed; @endif" 
                        class="rd-btn rd-btn-default mr-4" @disabled($desayuno_registrado || !$horarioPermitido)>
                            Agregar Desayuno
                        </button>
                        <button class="rd-btn rd-btn-primary w-90" type="submit" aria-label="Guardar desayunos"
                            @disabled($desayuno_registrado || !$horarioPermitido)
                            style="@if ($desayuno_registrado || !$horarioPermitido) opacity: 0.5; cursor: not-allowed; @endif">
                            Guardar Desayunos
                        </button>
                    </div>

                    <div class="rd-error mt-2" style="font-size: 1rem; text-align: left;">
                        @error('desayunos_agregados.' . $index . '.receta_id') 
                            <span class="text-danger">{{ $message }}</span> 
                        @enderror
                        @error('desayunos_agregados.' . $index . '.cantidad') 
                            <span class="text-danger">{{ $message }}</span> 
                        @enderror
                        @error('cantidad_servido')
                            <p>Ingrese una cantidad valida</p>
                        @enderror
                        @error('general')
                            <p>{{ $message }}</p>
                        @enderror
                        @error('duplicado')
                            <p>{{ $message }}</p>
                        @enderror
                        @if ($showNotification && $notification['type'] == 'danger')
                            <div class="rd-error mt-3 text-center" style="font-size: 1rem;">
                                {{ 'No hay suficiente inventario para esta receta' }}
                            </div>
                        @endif
                    </div>

                    @if (!$horarioPermitido)
                        <div
                            style="display: flex; align-items: center; padding: 1rem; margin-top: 1rem; background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 0.375rem; margin-inline: auto;">
                            <div style="flex-shrink: 0;">
                                <svg style="width: 1.5rem; height: 1.5rem; color: #ef4444;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div style="margin-left: 0.75rem;">
                                <h3 style="margin: 0; font-size: 0.875rem; font-weight: 500; color: #991b1b;">Horario no
                                    permitido</h3>
                                <div style="margin-top: 0.25rem; font-size: 0.875rem; color: #b91c1c;">
                                    <p style="margin: 0.25rem 0;">El registro de comedor solo está disponible de 6:00
                                        AM a 10:00 PM.</p>
                                    <p style="margin: 0.25rem 0 0;">Por favor, inténtalo de nuevo dentro del horario
                                        establecido.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($errors->has('hora'))
                        <div class="rd-alert rd-alert-error" role="alert">
                            <div class="rd-alert-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="rd-alert-content">
                                <h4 class="rd-alert-title">¡Error!</h3>
                                    <ul class="rd-alert-list">
                                        @foreach ($errors->all() as $error)
                                            <h4>{{ $error }}</h3>
                                        @endforeach
                                    </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <div class="rd-card-footer">
            <small class="text-muted">Una vez guardado, no se podrá modificar</small>
        </div>
    </div>
    <div>
        <div class="rd-card rd-card-list">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Registros De Comidas</h3>
                    <p class="rd-sub-sm">Últimos movimientos del día</p>
                </div>

                <div class="rd-actions">
                    <form action="" method="GET"
                        class="rd-search-inline" role="search">
                        <input name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input"
                            placeholder="Nombre de comida" id="search" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i
                                class="fas fa-search"></i></button>
                    </form>

                    <button class="rd-icon-btn"   aria-expanded="false"
                        aria-controls="filters" title="Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filters">
                <div class="rd-filters">
                    <form action="" method="GET"
                        class="rd-filters-form">
                        <div class="rd-filter-row">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" />
                        </div>
                        <div class="rd-filter-row">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                max="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="rd-filter-actions">
                            <button class="rd-btn rd-btn-primary" type="submit">Aplicar</button>
                            <button type="button" class="rd-btn rd-btn-default"
                                onclick="document.getElementById('fecha_desde').value=''; document.getElementById('fecha_hasta').value='';">Limpiar</button>
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
                                <th>Nombre Comida</th>
                                <th>Cantidad</th>
                                <th>Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $registro)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $registro->receta->nombre }}</td>
                                    <td>{{ $registro->cantidad_servido }}</td>
                                    <td>{{ \Carbon\Carbon::parse($registro->created_at)->format('d/m/Y') }}
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
                <div class="rd-pagination">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal', payload => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                    confirmButtonText: 'Aceptar'
                });
            });
        });
        document.addEventListener('livewire:initialized', () => {
            let isNotificationVisible = false;
            let hideTimeout = null;

            @this.on('notify-saved', () => {
                if (isNotificationVisible) {
                    return;
                }
                isNotificationVisible = true;
                hideTimeout = setTimeout(() => {
                    @this.set('showNotification', false);
                    isNotificationVisible = false;
                }, 3000);
            });
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

        const desdeDate = document.getElementById('fecha_desde');
        const hastaDate = document.getElementById('fecha_hasta');
        const fechaActual = new Date().toISOString().split('T')[0];
        if (desdeDate) desdeDate.max = fechaActual;
        if (hastaDate) hastaDate.max = fechaActual;
        if (desdeDate && hastaDate) {
            desdeDate.addEventListener('change', function() {
                if (!desdeDate.value) {
                    hastaDate.min = '';
                    return;
                }
                hastaDate.min = desdeDate.value;

                if (hastaDate.value && hastaDate.value < desdeDate.value) {
                    hastaDate.value = desdeDate.value;
                }
            });
            hastaDate.addEventListener('change', function() {
                if (!hastaDate.value || !desdeDate.value) {
                    return;
                }

                if (hastaDate.value < desdeDate.value) {
                    desdeDate.value = hastaDate.value;
                }
            });
        }
    </script>
@endpush
