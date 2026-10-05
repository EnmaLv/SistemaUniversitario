<div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', data => Swal.fire({
                icon:  data.icon,
                title: data.title,
                text:  data.text,
                confirmButtonColor: '#991b1b',
                timer: 4500,
                timerProgressBar: true,
            }));
        });
    </script>

    <div class="space-y-6">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm overflow-hidden">

            <div class="p-6 sm:p-8">

                <div class="mb-6">
                    <h2 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                        <i class="fas fa-file-import text-red-700 dark:text-red-500"></i>
                        Importar archivo de estudiantes
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Carga un archivo Excel (.xlsx o .xls) para registrar o actualizar la información académica.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6"
                    x-data="{
                        isUploading: false,
                        isProcessing: false,
                        progress: 0,
                        dragging: false,
                        fileName: '',
                        fileSize: '',
                        fileExt: '',

                        init() {
                            window.addEventListener('archivo-limpiar', () => this.resetFile());
                        },

                        openPicker() {
                            this.$refs.fileInput.click();
                        },

                        onChange(e) {
                            const f = e.target.files[0];
                            if (f) this.setInfo(f);
                        },

                        onDrop(e) {
                            this.dragging = false;
                            const files = e.dataTransfer?.files;
                            if (!files || !files.length) return;
                            this.$refs.fileInput.files = files;
                            this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            this.setInfo(files[0]);
                        },

                        setInfo(f) {
                            this.fileName = f.name;
                            this.fileExt  = (f.name.split('.').pop() || '').toUpperCase();
                            this.fileSize = this.formatSize(f.size);
                        },

                        resetFile() {
                            this.fileName = '';
                            this.fileSize = '';
                            this.fileExt  = '';
                            this.progress = 0;
                            this.isUploading = false;
                            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                        },

                        formatSize(bytes) {
                            if (bytes < 1024) return bytes + ' B';
                            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                            return (bytes / 1048576).toFixed(2) + ' MB';
                        },

                        async procesar() {
                            if (!this.fileName || this.isProcessing || this.isUploading) return;
                            this.isProcessing = true;
                            try {
                                await this.$wire.save();
                            } catch (e) {
                                console.error('[Archivos] Error al procesar:', e);
                            } finally {
                                this.isProcessing = false;
                            }
                        }
                    }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                >
                    <div class="lg:col-span-3">
                        <input type="file"
                            x-ref="fileInput"
                            wire:model="archivo"
                            wire:key="{{ $archivoKey }}"
                            accept=".xlsx,.xls"
                            class="hidden"
                            x-on:change="onChange($event)">

                        <div x-show="!fileName"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="onDrop($event)"
                            x-on:click="openPicker()"
                            class="cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition-all"
                            :class="dragging
                                ? 'border-red-500 bg-red-50/40 dark:bg-red-950/20 scale-[1.01]'
                                : 'hover:border-red-500 hover:bg-red-50/30 dark:hover:bg-red-950/10'"
                            style="border-color: var(--border-color);">

                            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center"
                                :class="dragging ? 'bg-red-100 dark:bg-red-950/40' : 'bg-red-50 dark:bg-red-950/30'">
                                <i class="fas fa-cloud-upload-alt text-2xl text-red-700 dark:text-red-500"></i>
                            </div>

                            <p class="font-bold text-base" style="color: var(--text-main);">
                                <span x-show="!dragging">Arrastra tu archivo aquí</span>
                                <span x-show="dragging" class="text-red-600 dark:text-red-400">Suelta el archivo</span>
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                o <span class="font-bold text-red-700 dark:text-red-500 underline">haz clic para seleccionar</span>
                            </p>
                            <p class="mt-3 text-[11px] text-gray-400 dark:text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Formatos permitidos: <strong>.xlsx, .xls</strong> · Tamaño máximo: <strong>10 MB</strong>
                            </p>
                        </div>

                        <div x-show="fileName" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="rounded-2xl border p-4 sm:p-5"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                            <div class="flex items-start gap-4">

                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-file-excel text-xl"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-bold truncate" style="color: var(--text-main);"
                                            x-text="fileName"></p>
                                        <span class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-black uppercase tracking-wider shrink-0"
                                            style="border-color: var(--border-color); color: var(--text-main);"
                                            x-text="fileExt"></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="fileSize"></span>
                                        <span class="mx-1.5">·</span>

                                        <span x-show="isUploading" class="text-amber-600 dark:text-amber-400 font-bold">
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i>Subiendo…
                                        </span>

                                        <span x-show="!isUploading && isProcessing" class="text-amber-600 dark:text-amber-400 font-bold">
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i>Procesando…
                                        </span>

                                        <span x-show="!isUploading && !isProcessing" class="text-emerald-600 dark:text-emerald-400 font-bold">
                                            <i class="fas fa-check-circle mr-1"></i>Listo para procesar
                                        </span>
                                    </p>
                                </div>

                                <button type="button"
                                    x-on:click="resetFile(); $wire.set('archivo', null)"
                                    x-show="!isUploading && !isProcessing"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors shrink-0"
                                    title="Quitar archivo">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <div x-show="isUploading" x-cloak class="mt-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Subiendo archivo
                                    </span>
                                    <span class="text-[11px] font-black tabular-nums" style="color: var(--text-main);"
                                        x-text="progress + '%'"></span>
                                </div>
                                <div class="h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-800">
                                    <div class="h-full rounded-full transition-all duration-200 bg-gradient-to-r from-red-700 to-red-500"
                                        :style="`width: ${progress}%`"></div>
                                </div>
                            </div>

                            <div x-show="isProcessing" x-cloak class="mt-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                        Procesando archivo en el servidor
                                    </span>
                                    <span class="text-[11px] font-bold text-gray-400">No cierres esta ventana</span>
                                </div>
                                <div class="h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-800 relative">
                                    <div class="absolute inset-y-0 w-1/3 rounded-full bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400 animate-indeterminate"></div>
                                </div>
                            </div>
                        </div>

                        @error('archivo')
                            <div class="mt-3 p-3 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 mt-0.5 text-sm"></i>
                                <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <div class="rounded-2xl border p-5 h-full flex flex-col"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                            <h3 class="font-extrabold text-sm uppercase tracking-wider flex items-center gap-2 mb-4"
                                style="color: var(--text-main);">
                                <i class="fas fa-list-check text-red-700 dark:text-red-500"></i>
                                Cómo funciona
                            </h3>

                            <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400 flex-1">
                                <li class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                    <span>Se validan columnas y tipos de datos antes de guardar.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                    <span>Los registros existentes se actualizan automáticamente por cédula.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </span>
                                    <span>Todo se ejecuta dentro de una transacción. Si algo falla, no se guarda nada.</span>
                                </li>
                            </ul>

                            <div class="mt-6 pt-5 border-t" style="border-color: var(--border-color);">

                                <div x-show="!fileName" class="text-center">
                                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mb-3">
                                        Selecciona un archivo para continuar
                                    </p>
                                    <button type="button" disabled
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed">
                                        <i class="fas fa-cloud-upload-alt text-xs"></i>
                                        Procesar archivo
                                    </button>
                                </div>

                                <div x-show="fileName" x-cloak>
                                    <button type="button"
                                        x-on:click="procesar()"
                                        x-bind:disabled="isProcessing || isUploading"
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-white shadow-lg transition
                                            bg-red-800 hover:bg-red-900 active:scale-[0.98]
                                            disabled:opacity-70 disabled:cursor-not-allowed">

                                        <span x-show="!isProcessing && !isUploading" class="inline-flex items-center gap-2">
                                            <i class="fas fa-cloud-upload-alt text-xs"></i>
                                            Procesar archivo
                                        </span>

                                        <span x-show="isProcessing" x-cloak class="inline-flex items-center gap-2">
                                            <i class="fas fa-circle-notch fa-spin text-xs"></i>
                                            Procesando…
                                        </span>

                                        <span x-show="!isProcessing && isUploading" x-cloak class="inline-flex items-center gap-2">
                                            <i class="fas fa-circle-notch fa-spin text-xs"></i>
                                            Subiendo…
                                        </span>
                                    </button>

                                    <p class="mt-3 text-[11px] text-center text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        El proceso puede tardar dependiendo del tamaño del archivo
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                            <i class="fas fa-history text-red-700 dark:text-red-500"></i>
                            Archivos procesados
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Historial de importaciones realizadas en el sistema.
                        </p>
                    </div>

                    <div class="relative w-full lg:w-80 lg:ml-auto">
                        <i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm"></i>
                        <input type="text" wire:model.live.debounce.300ms="buscar"
                            placeholder="Buscar archivo por nombre…"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto" id="printArea">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <th class="px-6 py-4 text-center" style="width:80px; color: var(--text-main);">#</th>
                            <th class="px-6 py-4 text-center" style="color: var(--text-main);">Archivo</th>
                            <th class="px-6 py-4 text-center" style="color: var(--text-main);">Fecha</th>
                            <th class="px-6 py-4 text-center" style="color: var(--text-main);">Estado</th>
                            <th class="px-6 py-4 text-center" style="width:120px; color: var(--text-main);">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs font-medium">
                        @forelse ($archivos as $archivo)
                            <x-table-row :id="$archivo->id" class="border-b" style="border-color: var(--border-color);">

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg border"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color: var(--text-main);">
                                    <div class="flex items-center justify-center gap-2">
                                        <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400"></i>
                                        <span>{{ basename($archivo->info_estudiantes) }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    <i class="far fa-calendar mr-1.5 opacity-60"></i>
                                    {{ \Carbon\Carbon::parse($archivo->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $archivo->estado }}
                                    </span>
                                </td>

                                <x-table-actions :id="$archivo->id" baseUrl="admin/configuracion/archivos"
                                    :show="false" :edit="false" :toggle="false">
                                    <a href="{{ url('/admin/configuracion/archivos/ver/' . $archivo->info_estudiantes) }}"
                                        target="_blank" onclick="event.stopPropagation()"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors"
                                        title="Ver archivo">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                </x-table-actions>
                            </x-table-row>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center">
                                            <i class="fas fa-inbox text-2xl text-gray-300 dark:text-gray-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold" style="color: var(--text-main);">
                                                No hay archivos registrados
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                Los archivos que proceses aparecerán aquí.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @keyframes indeterminate {
                0%   { transform: translateX(-100%); }
                100% { transform: translateX(400%); }
            }
            .animate-indeterminate {
                animation: indeterminate 1.4s ease-in-out infinite;
            }
            [x-cloak] { display: none !important; }
        </style>
    @endpush
</div>