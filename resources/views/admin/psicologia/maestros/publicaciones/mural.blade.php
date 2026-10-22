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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Mural de Avisos
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Mantente informado con los avisos y noticias del módulo de <strong
                        class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                </p>
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
                                    @if(isset($pub->profile_photo_path) && $pub->profile_photo_path)
                                        <img src="{{ route('media.profile_photos', basename($pub->profile_photo_path)) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        {{ substr($pub->psicologo->persona->nombres_per ?? 'P', 0, 1) }}{{ substr($pub->psicologo->persona->apellidos_per ?? '', 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold leading-tight" style="color: var(--text-main);">
                                        {{ explode(' ', trim($pub->psicologo->persona->nombres_per ?? ''))[0] ?: 'Psicólogo' }} {{ explode(' ', trim($pub->psicologo->persona->apellidos_per ?? ''))[0] ?? '' }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] font-medium text-gray-400">
                                            {{ \Carbon\Carbon::parse($pub->created_at)->translatedFormat('d M, Y · g:i A') }}
                                        </span>
                                        @if($pub->alcance === 'mis_pacientes')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 border border-{{ $themeColor }}-200 dark:border-{{ $themeColor }}-800">
                                                <i class="fas fa-user-lock text-[9px]"></i>
                                                Solo para ti
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            @if($pub->tipo === 'color')
                                <div
                                    class="rounded-2xl {{ $pub->color_fondo ?? 'bg-gray-800' }} p-8 sm:p-12 text-center aspect-video flex flex-col items-center justify-center shadow-inner relative overflow-hidden">
                                    <h3 class="text-xl sm:text-2xl font-black text-white leading-snug tracking-tight max-w-2xl">
                                        {{ $pub->titulo }}
                                    </h3>
                                    @if($pub->contenido)
                                        <p class="text-white/90 mt-4 text-xs sm:text-sm font-medium max-w-xl">
                                            {{ $pub->contenido }}
                                        </p>
                                    @endif
                                </div>
                            @elseif($pub->tipo === 'imagen' && $pub->media_path)
                                <h3 class="text-base font-extrabold mb-2 tracking-tight" style="color: var(--text-main);">
                                    {{ $pub->titulo }}
                                </h3>
                                @if($pub->contenido)
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                                        {{ $pub->contenido }}
                                    </p>
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
                                <h3 class="text-base font-extrabold mb-2 tracking-tight" style="color: var(--text-main);">
                                    {{ $pub->titulo }}
                                </h3>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                    {{ $pub->contenido }}
                                </div>
                            @endif
                        </div>

                        @php
                            $reaccionado = \Illuminate\Support\Facades\DB::table('publicacion_reacciones')
                                ->where('publicacion_id', $pub->id)
                                ->where('paciente_id', auth()->id())
                                ->exists();
                            
                            $likesCount = \Illuminate\Support\Facades\DB::table('publicacion_reacciones')
                                ->where('publicacion_id', $pub->id)
                                ->count();
                        @endphp
                        <div class="px-4 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between" 
                             x-data="{ 
                                reaccionado: {{ $reaccionado ? 'true' : 'false' }},
                                count: {{ $likesCount }},
                                loading: false,
                                toggle() {
                                    if(this.loading) return;
                                    this.loading = true;
                                    fetch('{{ route('admin.psicologia.maestros.publicaciones.reaccionar', $pub->id) }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        this.reaccionado = (data.status === 'added');
                                        this.count = data.total_likes;
                                    })
                                    .finally(() => {
                                        this.loading = false;
                                    });
                                }
                             }">
                            <button @click="toggle()" 
                                    :disabled="loading"
                                    :class="reaccionado ? 'text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                                    class="inline-flex items-center gap-2 text-xs font-bold transition-all active:scale-95 disabled:opacity-50">
                                <i class="fas fa-thumbs-up text-sm transition-transform" :class="reaccionado ? 'scale-110' : ''"></i>
                                <span x-text="reaccionado ? 'Te interesa' : 'Me interesa'"></span>
                            </button>
                            <div class="text-[11px] text-gray-400 font-semibold" x-show="count > 0" x-cloak>
                                <span x-text="count"></span> <span x-text="count == 1 ? 'persona' : 'personas'"></span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-1" style="color: var(--text-main);">Aún no hay publicaciones</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                            Vuelve pronto para ver las novedades e información relevante de nuestros psicólogos.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <div x-data="{ open: false, src: '' }"
             @open-image-modal.window="src = $event.detail; open = true"
             x-show="open" 
             style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="open = false"
             @click.self="open = false">
             
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