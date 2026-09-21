<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="mb-6">
                <a href="{{ route('admin.becas.solicitudes.index') }}"
                    class="inline-flex items-center text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-widest hover:text-red-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a solicitudes
                </a>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Detalle de solicitud
                        </h1>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Expediente de
                            <span class="font-bold">
                                {{ $solicitud->persona->nombre_persona }} {{ $solicitud->persona->apellido_persona }}
                            </span>
                            · C.I. {{ $solicitud->persona->cedula_persona }}
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest self-start sm:self-auto {{ $solicitud->estado_badge }}">
                        {{ $solicitud->estado_texto }}
                    </span>
                </div>
            </div>

            {{-- BLOQUE 1: DATOS GENERALES --}}
            <div class="rounded-2xl border shadow-sm mb-6 overflow-hidden"
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <div class="px-6 py-4 border-b"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                    <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-user-graduate text-red-700"></i> Datos generales
                    </h3>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Estudiante</span>
                        <span class="font-bold" style="color: var(--text-main);">
                            {{ $solicitud->persona->nombre_persona }} {{ $solicitud->persona->apellido_persona }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Cédula</span>
                        <span class="font-bold font-mono" style="color: var(--text-main);">
                            {{ $solicitud->persona->cedula_persona }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">PNF</span>
                        <span class="font-bold" style="color: var(--text-main);">
                            {{ $solicitud->persona->personaPnf->first()->pnf->nombre_pnf ?? 'No registrado' }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Beneficio</span>
                        <span class="font-bold text-red-700 dark:text-red-400">
                            {{ $solicitud->beneficio->nombre_beneficio ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Jornada</span>
                        <span class="font-semibold" style="color: var(--text-main);">
                            {{ $solicitud->jornada->nombre_jornada ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Lapso</span>
                        <span class="font-semibold font-mono" style="color: var(--text-main);">
                            {{ $solicitud->lapso->codigo ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Tipo de
                            solicitud</span>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest
                            {{ $solicitud->tipo_solicitud === 'nueva'
    ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900'
    : 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/30 dark:text-purple-400 dark:border-purple-900' }}">
                            {{ ucfirst($solicitud->tipo_solicitud) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Fecha de
                            envío</span>
                        <span class="font-semibold" style="color: var(--text-main);">
                            {{ $solicitud->created_at?->format('d/m/Y g:i A') ?? '—' }}
                        </span>
                    </div>
                    @if ($solicitud->documento)
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Archivo
                                adjunto</span>
                            <a href="{{ asset('storage/' . $solicitud->documento->ruta_archivo) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border bg-red-50 text-red-700 border-red-200 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900 transition-colors text-[11px] font-black uppercase tracking-wider">
                                <i class="fas fa-file-pdf"></i> Ver Notas
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- BLOQUE 2: RESPUESTAS DEL FORMULARIO --}}
            <div class="rounded-2xl border shadow-sm mb-6 overflow-hidden"
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <div class="px-6 py-4 border-b flex items-center justify-between"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                    <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-clipboard-list text-red-700"></i> Respuestas del formulario
                    </h3>
                    @if ($solicitud->respuestas->isNotEmpty())
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                            {{ $solicitud->respuestas->count() }} respuesta(s)
                        </span>
                    @endif
                </div>

                @php
                    $respuestasOrdenadas = $solicitud->respuestas
                        ->sortBy(fn($r) => $r->pregunta?->orden ?? 0)
                        ->values();
                    $eliminatoriasFallidas = $respuestasOrdenadas->where('cumple_criterio', false)->count();
                @endphp

                @if ($eliminatoriasFallidas > 0)
                    <div class="mx-6 mt-4 rounded-xl border px-4 py-3 flex items-start gap-3
                            bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900">
                        <i class="fas fa-triangle-exclamation text-rose-600 dark:text-rose-400 mt-0.5"></i>
                        <div class="text-xs text-rose-700 dark:text-rose-300 font-semibold">
                            <p class="font-black uppercase tracking-wider mb-0.5">Criterios eliminatorios no cumplidos</p>
                            <p>Esta solicitud no cumple {{ $eliminatoriasFallidas }} criterio(s) eliminatorio(s). Considera
                                esto al verificar.</p>
                        </div>
                    </div>
                @endif

                <div class="p-6">
                    @forelse ($respuestasOrdenadas as $resp)
                        @php
                            $pregunta = $resp->pregunta;
                            $esEliminatoria = $pregunta && $pregunta->criterios->contains(fn($c) => $c->es_eliminatoria);
                        @endphp

                        <div class="py-4 border-b last:border-0 flex flex-col sm:flex-row sm:items-start gap-3"
                            style="border-color: var(--border-color);">

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $pregunta->etiqueta ?? 'Pregunta eliminada' }}
                                    </span>
                                    @if ($pregunta && $pregunta->codigo)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800 font-mono">
                                            {{ $pregunta->codigo }}
                                        </span>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                    @if ($pregunta && $pregunta->tipo === 'checkbox' && !empty($resp->valor_json))
                                        <div class="flex flex-wrap gap-1.5 mt-1">
                                            @foreach ($resp->valor_json as $v)
                                                @php
                                                    $opt = $pregunta->opciones->firstWhere('valor', $v);
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-md
                                                                bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400
                                                                border border-sky-200 dark:border-sky-900">
                                                    {{ $opt->etiqueta ?? $v }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif ($pregunta && in_array($pregunta->tipo, ['select', 'radio', 'boolean']))
                                        @php
                                            $opt = $pregunta->opciones->firstWhere('valor', $resp->valor);
                                            $texto = $opt->etiqueta ?? $resp->valor;
                                            if ($pregunta->tipo === 'boolean') {
                                                $texto = $resp->valor == '1' ? 'Sí' : ($resp->valor == '0' ? 'No' : $resp->valor);
                                            }
                                        @endphp
                                        <span class="font-medium">{{ $texto ?: '—' }}</span>
                                    @else
                                        <span class="font-medium whitespace-pre-line">{{ $resp->valor ?: '—' }}</span>
                                    @endif
                                </div>
                            </div>

                            @if ($resp->cumple_criterio !== null)
                                @if ($resp->cumple_criterio)
                                    <span class="inline-flex items-center gap-1 self-start px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg
                                                    bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400
                                                    border border-emerald-200 dark:border-emerald-900">
                                        <i class="fas fa-check text-[8px]"></i> Cumple
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 self-start px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg
                                                    bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400
                                                    border border-rose-200 dark:border-rose-900">
                                        <i class="fas fa-times text-[8px]"></i>
                                        No cumple{{ $esEliminatoria ? ' · Eliminatorio' : '' }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    @empty
                        <div
                            class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <i class="fas fa-inbox text-2xl mb-2 block text-gray-300 dark:text-gray-700"></i>
                            Esta solicitud no tiene respuestas registradas
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- BLOQUE 3: AUDITORÍA --}}
            <div class="rounded-2xl border shadow-sm overflow-hidden"
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <div class="px-6 py-4 border-b"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                    <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-shield-halved text-red-700"></i> Auditoría y verificación
                    </h3>
                </div>

                <div class="p-6">
                    @if ($solicitud->estado === 0)
                        <form action="{{ route('admin.becas.solicitudes.verificar', $solicitud->id) }}" method="POST"
                            id="form-verificar">
                            @csrf
                            @method('PUT')

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                                Revisa las respuestas cargadas. Puedes aprobar directamente o rechazar indicando el motivo.
                            </p>

                            <div class="mb-5">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                    Decisión *
                                </label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-xl border transition-all
                                            bg-emerald-50 dark:bg-emerald-950/20
                                            border-emerald-200 dark:border-emerald-900
                                            hover:bg-emerald-100 dark:hover:bg-emerald-950/40">
                                        <input type="radio" name="estado" value="1" onchange="toggleComentario()" required
                                            class="text-emerald-600 focus:ring-emerald-500">
                                        <span
                                            class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                            Aprobar
                                        </span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-xl border transition-all
                                            bg-rose-50 dark:bg-rose-950/20
                                            border-rose-200 dark:border-rose-900
                                            hover:bg-rose-100 dark:hover:bg-rose-950/40">
                                        <input type="radio" name="estado" value="2" onchange="toggleComentario()" required
                                            class="text-rose-700 focus:ring-rose-500">
                                        <span
                                            class="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-400">
                                            Rechazar
                                        </span>
                                    </label>
                                </div>
                                @error('estado')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-5 hidden" id="div-comentario">
                                <label for="comentario_verificador"
                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                    Motivo del rechazo *
                                </label>
                                <textarea name="comentario_verificador" id="comentario_verificador" rows="3"
                                    placeholder="Indica el motivo detallado del rechazo..."
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                                @error('comentario_verificador')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t" style="border-color: var(--border-color);">
                                <a href="{{ route('admin.becas.solicitudes.index') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                                    style="border-color: var(--border-color); color: var(--text-main);">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="rd-submit-btn inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                                    <i class="fas fa-check-circle text-xs"></i> Guardar decisión
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <span
                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Verificado
                                    por</span>
                                <span class="font-bold" style="color: var(--text-main);">
                                    {{ $solicitud->verificador->persona->nombre_persona ?? 'N/A' }}
                                    {{ $solicitud->verificador->persona->apellido_persona ?? '' }}
                                </span>
                                <span class="block text-[10px] text-gray-400 font-mono mt-0.5">
                                    {{ $solicitud->verificador->username ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Fecha
                                    de verificación</span>
                                <span class="font-semibold" style="color: var(--text-main);">
                                    {{ $solicitud->fecha_verificacion?->format('d/m/Y g:i A') ?? 'N/A' }}
                                </span>
                            </div>

                            @if ($solicitud->estado === 2 && $solicitud->comentario_verificador)
                                <div class="sm:col-span-2 md:col-span-3">
                                    <span
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Motivo
                                        del rechazo</span>
                                    <div class="rounded-xl border px-4 py-3 text-sm font-medium
                                                bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-300
                                                border-rose-200 dark:border-rose-900">
                                        {{ $solicitud->comentario_verificador }}
                                    </div>
                                </div>
                            @endif

                            @if ($solicitud->estado === 1)
                                <div class="mt-6 flex justify-end border-t pt-4" style="border-color: var(--border-color);">
                                    <button type="button" onclick="document.getElementById('modalRevocar').classList.replace('hidden', 'flex')"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 hover:border-red-300 dark:bg-red-950/20 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-900/40 text-sm font-bold shadow-sm transition-all">
                                        <i class="fas fa-ban text-xs"></i> Revocar Beca
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if ($solicitud->estado === 1)
        <div id="modalRevocar" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border shadow-2xl"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--border-color);">
                    <h3 class="text-lg font-semibold text-red-600 dark:text-red-500">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Revocar Beca
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-red-600 transition-colors"
                        onclick="document.getElementById('modalRevocar').classList.replace('flex', 'hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.becas.solicitudes.verificar', $solicitud->id) }}" method="POST" class="p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="estado" value="2">
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Está a punto de revocar esta beca. Esta acción cancelará el beneficio y liberará los cupos asignados. 
                        Por favor, indique el motivo.
                    </p>

                    <div class="mb-5">
                        <label for="comentario_verificador_revocar"
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                            Motivo de la revocación *
                        </label>
                        <textarea name="comentario_verificador" id="comentario_verificador_revocar" rows="4"
                            placeholder="Indique el motivo detallado de la revocación..." required
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-xl border px-5 py-2.5 text-sm font-bold transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                            style="border-color: var(--border-color); color: var(--text-main);"
                            onclick="document.getElementById('modalRevocar').classList.replace('flex', 'hidden')">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="rd-submit-btn inline-flex items-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-red-900 active:scale-95">
                            <i class="fas fa-ban text-xs"></i> Confirmar Revocación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        function toggleComentario() {
            const radioRechazar = document.querySelector('input[name="estado"][value="2"]');
            const divComentario = document.getElementById('div-comentario');
            const inputComentario = document.getElementById('comentario_verificador');

            if (!radioRechazar || !divComentario) return;

            if (radioRechazar.checked) {
                divComentario.classList.remove('hidden');
                inputComentario.setAttribute('required', 'required');
            } else {
                divComentario.classList.add('hidden');
                inputComentario.removeAttribute('required');
                inputComentario.value = '';
            }
        }
    </script>
</x-app-layout>