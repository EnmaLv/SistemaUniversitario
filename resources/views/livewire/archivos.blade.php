<div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', data => Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
                confirmButtonColor: '#991b1b',
                timer: 3000,
                timerProgressBar: true
            }));
        });
    </script>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden mb-6">
        <div class="p-6">
            <div class="mb-5">
                <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Subir archivo</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Carga un archivo de estudiantes para registrar o actualizar la información en el sistema.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <label for="archivo" class="block cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition hover:border-red-500 hover:bg-red-50/30 dark:hover:bg-red-950/10"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-file-upload mb-3 text-3xl text-red-700 dark:text-red-500"></i>
                        <p class="font-bold" style="color: var(--text-main);">Selecciona el archivo manualmente</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Formatos permitidos: Excel (.xlsx, .xls)</p>
                        <input type="file" id="archivo" hidden wire:model="archivo" wire:key="{{ $archivoKey }}" accept=".xlsx,.xls,.pdf,.txt">
                    </label>
                    @error('archivo')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                    <div x-data="{ isUploading: false, progress: 0 }"
                        x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <div x-show="isUploading" class="mt-3">
                            <progress max="100" x-bind:value="progress" class="w-full h-3"></progress>
                        </div>
                        <span wire:ignore id="nombre-display"></span>
                    </div>
                </div>

                <div class="rounded-2xl border p-5" style="border-color: var(--border-color); background-color: var(--input-bg);">
                    <h3 class="font-extrabold" style="color: var(--text-main);">¿Cómo funciona?</h3>
                    <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Se validan columnas y tipos de datos.</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Los errores se reportan antes de guardar.</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>La inserción se realiza en una transacción.</li>
                    </ul>
                    <div class="mt-5 flex justify-end">
                        <button wire:click="save"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-cloud-upload-alt text-xs"></i>
                            Procesar archivo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-2.5 border-b" style="border-color: var(--border-color);">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div>
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Archivos procesados</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Historial de importaciones realizadas.</p>
                </div>
                <div class="relative w-full lg:w-80 lg:ml-auto">
                    <i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400"></i>
                    <input type="text" wire:model.live="buscar" placeholder="Buscar archivo..."
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto" id="printArea">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4 text-center" style="width:80px;">#</th>
                        <th class="px-6 py-4 text-center">Archivo</th>
                        <th class="px-6 py-4 text-center">Fecha</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center" style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-medium">
                    @forelse ($archivos as $archivo)
                        <x-table-row :id="$archivo->id" class="border-b" style="border-color: var(--border-color);">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                    {{ $loop->iteration }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color: var(--text-main);">
                                {{ basename($archivo->info_estudiantes) }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($archivo->fecha)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                    <i class="fas fa-check-circle"></i> {{ $archivo->estado }}
                                </span>
                            </td>
                            <x-table-actions :id="$archivo->id" baseUrl="admin/configuracion/archivos" :show="false" :edit="false" :toggle="false">
                                <a href="{{ url('/admin/configuracion/archivos/ver/' . $archivo->info_estudiantes) }}"
                                    target="_blank" onclick="event.stopPropagation()"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                                    title="Ver archivo">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                            </x-table-actions>
                        </x-table-row>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay archivos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
