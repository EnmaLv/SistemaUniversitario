<x-app-layout>
    @php
        $moduloActivo = strtolower(session('modulo_activo', 'general'));
        $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);
        $themeColor = $esPsicologia ? 'indigo' : 'blue';
        $btnClass = $esPsicologia ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-blue-600 hover:bg-blue-700';
        $focusRingClass = $esPsicologia
            ? 'focus:ring-indigo-500/20 focus:border-indigo-500'
            : 'focus:ring-blue-500/20 focus:border-blue-500';
    @endphp

    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Avisos y Comunicados
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestión y emisión de avisos informativos para <strong
                            class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @canRoute('admin.psicologia.maestros.publicaciones.create')
                    <a href="{{ route('admin.psicologia.maestros.publicaciones.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Nuevo Anuncio</span>
                    </a>
                    @endcanRoute
                </div>
            </div>
            <div class="space-y-6">
                @forelse($publicaciones as $pub)
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm overflow-hidden transition-all">
                        <div
                            class="p-4 sm:p-5 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/60">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center font-bold text-sm overflow-hidden border border-{{ $themeColor }}-100 dark:border-{{ $themeColor }}-900/30">
                                    @if (auth()->user()->profile_photo_path)
                                        <img src="{{ route('media.profile_photos', basename(auth()->user()->profile_photo_path)) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        {{ substr(auth()->user()->persona->nombres_per, 0, 1) }}{{ substr(auth()->user()->persona->apellidos_per, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold leading-tight" style="color: var(--text-main);">
                                        {{ auth()->user()->persona->nombres_per }} {{ auth()->user()->persona->apellidos_per }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] font-medium text-gray-400">
                                            {{ \Carbon\Carbon::parse($pub->created_at)->translatedFormat('d M, Y · g:i A') }}
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider
                                            {{ $pub->alcance === 'todos'
                                                ? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
                                                : 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' }}">
                                            <i
                                                class="fas {{ $pub->alcance === 'todos' ? 'fa-globe' : 'fa-users' }} text-[9px]"></i>
                                            {{ $pub->alcance === 'todos' ? 'Público' : 'Segmentado' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if (auth()->user()->puedeRuta('admin.psicologia.maestros.publicaciones.edit') || auth()->user()->puedeRuta('admin.psicologia.maestros.publicaciones.destroy'))
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                    <i class="fas fa-ellipsis-vertical text-sm"></i>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    style="background-color: var(--bg-card); border-color: var(--border-color);"
                                    class="absolute right-0 mt-2 w-44 rounded-xl shadow-xl border divide-y divide-gray-100 dark:divide-gray-800 z-50 overflow-hidden"
                                    style="display: none;">
                                    @canRoute('admin.psicologia.maestros.publicaciones.edit')
                                    <div class="py-1">
                                        <a href="{{ route('admin.psicologia.maestros.publicaciones.edit', $pub->id) }}"
                                            class="group flex items-center px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition">
                                            <i
                                                class="fas fa-pen-to-square mr-2.5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 text-xs"></i>
                                            Editar
                                        </a>
                                    </div>
                                    @endcanRoute
                                    @canRoute('admin.psicologia.maestros.publicaciones.destroy')
                                    <div class="py-1">
                                        <form
                                            action="{{ route('admin.psicologia.maestros.publicaciones.destroy', $pub->id) }}"
                                            method="POST"
                                            onsubmit="event.preventDefault(); AppModal.show('Eliminar Publicación', '¿Seguro que deseas eliminar esta publicación?', { type: 'confirm', intent: 'danger' }).then(c => { if(c) this.submit(); });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="group flex w-full items-center px-4 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                                <i
                                                    class="fas fa-trash-can mr-2.5 text-rose-500 group-hover:text-rose-600 text-xs"></i>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                    @endcanRoute
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="p-4 sm:p-6">
                            @if ($pub->tipo === 'color')
                                <div
                                    class="rounded-2xl {{ $pub->color_fondo ?? 'bg-gray-800' }} p-8 sm:p-12 text-center aspect-video flex flex-col items-center justify-center shadow-inner relative overflow-hidden">
                                    <h3
                                        class="text-xl sm:text-2xl font-black text-white leading-snug tracking-tight max-w-2xl">
                                        {{ $pub->titulo }}</h3>
                                    @if ($pub->contenido)
                                        <p class="text-white/90 mt-4 text-xs sm:text-sm font-medium max-w-xl">
                                            {{ $pub->contenido }}</p>
                                    @endif
                                </div>
                            @elseif($pub->tipo === 'imagen' && $pub->media_path)
                                <h3 class="text-base font-extrabold mb-2 tracking-tight"
                                    style="color: var(--text-main);">{{ $pub->titulo }}</h3>
                                @if ($pub->contenido)
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                                        {{ $pub->contenido }}</p>
                                @endif
                                <div
                                    class="rounded-2xl overflow-hidden bg-gray-100 dark:bg-black/40 border border-gray-100 dark:border-gray-800 shadow-inner group relative">
                                    <img @click="$dispatch('open-image-modal', '{{ route('media.publicaciones', basename($pub->media_path)) }}')"
                                        src="{{ route('media.publicaciones', basename($pub->media_path)) }}"
                                        class="w-full h-auto object-cover max-h-[500px] cursor-pointer group-hover:scale-[1.01] transition-transform duration-300">
                                    <div
                                        class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none flex items-center justify-center">
                                        <span
                                            class="bg-black/60 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-xl flex items-center gap-2">
                                            <i class="fas fa-expand text-xs"></i> Ampliar imagen
                                        </span>
                                    </div>
                                </div>
                            @else
                                <h3 class="text-base font-extrabold mb-2 tracking-tight"
                                    style="color: var(--text-main);">{{ $pub->titulo }}</h3>
                                <div
                                    class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                    {{ $pub->contenido }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-1" style="color: var(--text-main);">Sin publicaciones
                            registradas</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
                            No has creado ninguna publicación aún. Haz clic en "Nuevo Anuncio" para realizar tu primer
                            comunicado.
                        </p>
                        @canRoute('admin.psicologia.maestros.publicaciones.create')
                        <a href="{{ route('admin.psicologia.maestros.publicaciones.create') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Nuevo Anuncio</span>
                        </a>
                        @endcanRoute
                    </div>
                @endforelse
            </div>
        </div>

        <div x-data="{ open: false, src: '' }" @open-image-modal.window="src = $event.detail; open = true" x-show="open"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false" @click.self="open = false">

            <button @click="open = false"
                class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center text-white hover:text-gray-300 rounded-xl bg-black/50 hover:bg-black/80 transition-colors">
                <i class="fas fa-xmark text-lg"></i>
            </button>

            <div class="max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl border border-white/10">
                <img :src="src" class="w-full h-full object-contain max-h-[85vh]" @click.stop>
            </div>
        </div>
    </div>
</x-app-layout>
