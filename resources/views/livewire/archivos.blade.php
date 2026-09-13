<div class="main-content pt-3">
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center content-header">
        <div>
            <h1 class="m-0 rd-title-sm" style="font-size:1.4rem">
                Importación de Archivos
            </h1>
            <p class="mt-1 mb-0" style="color:#64748b">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
                Sube los archivos para registrar la información de los estudiantes directamente en el sistema.
            </p>
        </div>

        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Hoy</small>
                <span style="font-weight:600; font-size:0.95rem;">
                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                </span>
            </div>

            <div
                style="width:46px;height:46px;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(15,23,42,0.08);">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                    style="width:100%; height:100%; object-fit:cover;">
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', data => {
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                    confirmButtonColor: '#7c3aed',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        });
    </script>

    <div class="rd-card mb-4">
        <div class="rd-card-body">

            <h3 class="rd-title-sm mb-3">
                Subir archivo
            </h3>

            <div class="row g-3">

                <div class="col-md-7">

                    <label class="rd-card p-4 text-center" for="archivo">

                        <i class="fas fa-file-upload mb-2" style="font-size:2.2rem; color:#64748b"></i>

                        <p class="mb-1 fw-semibold">
                            Selecciónalo manualmente
                        </p>

                        <p class="mb-3" style="font-size:.85rem; color:#64748b">
                            Formatos permitidos: Excel (.xlsx)
                        </p>

                        <div style="text-align: left;" x-data="{ isUploading: false, progress: 0 }"
                            x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <input type="file" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" wire:model="archivo"
                                wire:key="{{ $archivoKey }}" accept=".xlsx,.xls"
                                style="padding: 20px; font-size: 1rem; height: auto;" id="archivo" hidden>

                            @error('archivo')
                                <div class="rd-error">{{ $message }}</div>
                            @enderror
                            <div x-show="isUploading" style="text-align: left; margin-top: 12px;">
                                <progress max="100" x-bind:value="progress"
                                    style="width: 100%; height: 16px;"></progress>
                            </div>
                            <span wire:ignore id="nombre-display"></span>
                        </div>
                    </label>
                </div>
                <div class="col-md-5">
                    <label class="rd-label mb-1">¿Cómo funciona?</label>

                    <div class="rd-card p-3" style="background:#fbfdff">
                        <p class="mb-2">
                            El sistema leerá el archivo y convertirá cada fila en un
                            registro de base de datos.
                        </p>

                        <ul class="mb-0" style="font-size:.9rem; color:#475569">
                            <li>Se validan columnas y tipos de datos</li>
                            <li>Los errores se reportan antes de guardar</li>
                            <li>La inserción se realiza en una transacción</li>
                        </ul>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button wire:click="save" class="rd-btn rd-btn-primary">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Procesar archivo
                            </button>

                        </div>
                    </div>

                </div>

            </div>


        </div>
    </div>

    {{-- HISTORIAL --}}
    <div class="rd-card">
        <div class="rd-card-body">

            <div class="rd-card-header rd-header-space">
                <h3 class="rd-title-sm">
                    Archivos procesados
                </h3>

                <div class="rd-search-inline">
                    <input type="text" class="rd-search-input" placeholder="Buscar archivo..."
                        wire:model.live="buscar">
                </div>
            </div>

            <table class="rd-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archivos as $archivo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ basename($archivo->info_estudiantes) }}</td>
                            <td>{{ \Carbon\Carbon::parse($archivo->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="rd-badge rd-badge-success">
                                    {{ $archivo->estado }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ url('/admin/configuracion/archivos/ver/' . $archivo->info_estudiantes) }}"
                                    target="_blank" class="rd-action" title="Ver archivo">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No hay archivos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

@push('js')
    <script src="{{ asset('js/localidad.js') }}"></script>
@endpush
