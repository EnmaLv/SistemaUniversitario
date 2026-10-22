@php
    $actividad = $bloque->calendario;
    $torneo = $bloque->torneos->sortByDesc('id_tde')->first();
    $fecha = $sesion->fecha_sad;
    $titulo = $torneo?->nombre_tde ?? $actividad?->nombre_cld;
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $resultadosPrevios = session('resultados_asistencia', []);
    $config = [
        'estado'     => $estado,
        'resultados' => $resultadosPrevios,
        'urlMarcar'  => route('admin.deportes.asistencia.store_presentes', $sesion->id_sad),
        'urlQuitar'  => route('admin.deportes.asistencia.store_quitar', $sesion->id_sad),
        'token'      => csrf_token(),
        'puedeQuitar' => \Illuminate\Support\Facades\Auth::user()?->puedeRuta('admin.deportes.asistencia.store_quitar') ?? false,
    ];
@endphp
<x-app-layout>

    @include('components.alert')

    <script>
        window.asistenciaDeportiva = function (config) {
            return {
                estado: config.estado,
                resultados: config.resultados || [],
                cedula: '',
                lista: '',
                enviando: false,
                error: '',
                async enviar(texto) {
                    texto = (texto || '').trim();
                    if (!texto || this.enviando) {
                        return;
                    }
                    this.enviando = true;
                    this.error = '';
                    try {
                        const respuesta = await fetch(config.urlMarcar, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.token, 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({ cedulas: texto })
                        });
                        const datos = await respuesta.json();
                        if (!respuesta.ok) {
                            this.error = datos.mensaje || datos.message || 'No se pudo guardar. Intenta de nuevo.';
                            return;
                        }
                        this.estado = datos.estado;
                        this.resultados = datos.resultados.concat(this.resultados).slice(0, 50);
                        this.cedula = '';
                        this.lista = '';
                    } catch (e) {
                        this.error = 'No hay conexión con el servidor. Revisa tu conexión e intenta de nuevo.';
                    } finally {
                        this.enviando = false;
                        this.$nextTick(() => this.$refs.cedula && this.$refs.cedula.focus());
                    }
                },
                async quitar(id, nombre) {
                    if (!confirm('¿Quitar a ' + nombre + ' de los presentes?')) {
                        return;
                    }
                    try {
                        const respuesta = await fetch(config.urlQuitar, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.token, 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({ asistencia_id: id })
                        });
                        const datos = await respuesta.json();
                        if (!respuesta.ok) {
                            this.error = datos.mensaje || datos.message || 'No se pudo quitar.';
                            return;
                        }
                        this.estado = datos.estado;
                    } catch (e) {
                        this.error = 'No hay conexión con el servidor.';
                    }
                },
                clase(resultado) {
                    return {
                        marcada: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        no_inscrita: 'bg-amber-50 text-amber-800 border-amber-200',
                        ya_marcada: 'bg-sky-50 text-sky-800 border-sky-200',
                        no_encontrada: 'bg-red-50 text-red-800 border-red-200',
                        invalida: 'bg-red-50 text-red-800 border-red-200'
                    }[resultado] || 'bg-gray-50 text-gray-700 border-gray-200';
                },
                puedeQuitar: config.puedeQuitar
            };
        };
    </script>

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]" x-data="asistenciaDeportiva(@js($config))">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.asistencia.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a la lista de eventos
            </a>

            <div class="mt-2 mb-5 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $titulo }}</h1>
                    <p class="mt-1 text-sm font-semibold" style="color:var(--text-main);">
                        {{ $bloque->actividad?->nombre_acd }} · {{ $bloque->espacio?->nombre_epd }} ·
                        {{ \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($fecha)] }} {{ $fecha->format('d/m/Y') }} · {{ $bloque->horas() }}
                    </p>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">
                        @if ($sesion->cerrada_sad)
                            Esta asistencia está cerrada. Puedes ver el reporte o, si hay un error, reabrirla.
                        @else
                            Escribe la cédula de cada persona presente y pulsa Enter. Cuando termines, pulsa "Cerrar asistencia".
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.asistencia.reporte')
                    <a href="{{ route('admin.deportes.asistencia.reporte', $sesion->id_sad) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-list text-xs"></i> Ver reporte
                    </a>
                    @endcanRoute
                </div>
            </div>

            @include('admin.deportes.partials.errores')

            <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border p-4 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Presentes</p>
                    <p class="text-3xl font-black text-emerald-600" x-text="estado.total_presentes">{{ $estado['total_presentes'] }}</p>
                </div>
                <template x-if="estado.usa_inscritos">
                    <div class="rounded-2xl border p-4 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">{{ $actividad?->esTorneo() ? 'Integrantes presentes' : 'Inscritos presentes' }}</p>
                        <p class="text-3xl font-black" style="color:var(--text-main);"><span x-text="estado.presentes_inscritos"></span> <span class="text-base font-bold" style="color:var(--text-muted);">de <span x-text="estado.total_inscritos"></span></span></p>
                    </div>
                </template>
                <div class="col-span-2 rounded-2xl border p-4 text-center shadow-sm sm:col-span-1" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Estado</p>
                    <p class="mt-1 text-lg font-extrabold {{ $sesion->cerrada_sad ? 'text-emerald-700' : 'text-amber-600' }}">{{ $sesion->cerrada_sad ? 'Cerrada' : 'Abierta' }}</p>
                </div>
            </div>

            <p class="mb-5 text-sm font-bold" style="color:var(--text-main);" x-show="estado.usa_inscritos">
                Presentes <span x-text="estado.presentes_inscritos"></span> de <span x-text="estado.total_inscritos"></span> {{ $actividad?->esTorneo() ? 'integrantes de los equipos' : 'inscritos' }}
                <span class="font-normal" style="color:var(--text-muted);" x-show="estado.total_presentes > estado.presentes_inscritos">(y <span x-text="estado.total_presentes - estado.presentes_inscritos"></span> sin inscripción)</span>
            </p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                <div class="space-y-5 lg:col-span-2">
                    @if (! $sesion->cerrada_sad)
                        @canRoute('admin.deportes.asistencia.store_presentes')
                        <form method="POST" action="{{ route('admin.deportes.asistencia.store_presentes', $sesion->id_sad) }}" @submit.prevent="enviar(cedula)"
                            class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);">
                            @csrf
                            <label for="cedula" class="block text-sm font-extrabold" style="color:var(--text-main);">Cédula de la persona presente</label>
                            <div class="flex gap-2">
                                <input id="cedula" x-ref="cedula" type="text" name="cedulas" x-model="cedula" autocomplete="off" autofocus inputmode="numeric" placeholder="Ej. 12345678"
                                    class="w-full rounded-xl border px-3 py-3 text-lg font-bold" style="{{ $estiloCampo }}">
                                <button type="submit" class="rounded-xl bg-red-800 px-4 text-sm font-extrabold text-white hover:bg-red-900" :disabled="enviando">
                                    <span x-show="!enviando">Marcar</span><span x-show="enviando" style="display:none;">...</span>
                                </button>
                            </div>
                            <p class="text-xs" style="color:var(--text-muted);">Escribe la cédula y pulsa Enter. El campo queda listo para la siguiente.</p>
                        </form>

                        <form method="POST" action="{{ route('admin.deportes.asistencia.store_presentes', $sesion->id_sad) }}" @submit.prevent="enviar(lista)"
                            class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);">
                            @csrf
                            <label for="lista" class="block text-sm font-extrabold" style="color:var(--text-main);">¿Tienes una lista en papel? Pega varias cédulas</label>
                            <textarea id="lista" name="cedulas" rows="5" x-model="lista" class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}" placeholder="Una por línea o separadas por coma:&#10;12345678&#10;23456789, 34567890"></textarea>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);" :disabled="enviando">
                                <i class="fas fa-paste text-xs"></i> Marcar todas
                            </button>
                        </form>
                        @endcanRoute

                        @canRoute('admin.deportes.asistencia.store_cierre')
                        <form method="POST" action="{{ route('admin.deportes.asistencia.store_cierre', $sesion->id_sad) }}" class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);"
                            data-confirmar="¿Cerrar la asistencia?" data-confirmar-texto="Los inscritos que no marcaste quedarán como inasistentes. Si te equivocas, alguien con permiso de edición podrá reabrirla." data-confirmar-boton="Sí, cerrar">
                            @csrf
                            <p class="text-sm" style="color:var(--text-muted);">¿Terminaste? Al cerrar, {{ $actividad?->usaInscritos() ? 'los inscritos que no marcaste quedan como inasistentes.' : 'la lista de presentes queda guardada.' }}</p>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">
                                <i class="fas fa-lock text-xs"></i> Cerrar asistencia
                            </button>
                        </form>
                        @endcanRoute
                    @else
                        <div class="rounded-2xl border p-5 shadow-sm space-y-3 text-sm" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                            <p><i class="fas fa-lock mr-1 text-emerald-700"></i> Cerrada el {{ $sesion->cerrada_en_sad?->format('d/m/Y h:i A') }}{{ $sesion->cerradaPor?->persona ? ' por ' . trim($sesion->cerradaPor->persona->nombres_per . ' ' . $sesion->cerradaPor->persona->apellidos_per) : '' }}.</p>
                            @canRoute('admin.deportes.asistencia.reabrir')
                            <form method="POST" action="{{ route('admin.deportes.asistencia.reabrir', $sesion->id_sad) }}" class="space-y-2"
                                data-confirmar="¿Reabrir la asistencia?" data-confirmar-texto="Quedará registrado en la bitácora. Los inasistentes vuelven a estar pendientes hasta que la cierres otra vez." data-confirmar-boton="Sí, reabrir">
                                @csrf
                                <label for="motivo" class="block text-xs font-bold" style="color:var(--text-muted);">Motivo (opcional)</label>
                                <input id="motivo" type="text" name="motivo" maxlength="500" class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}" placeholder="Ej. faltó marcar a dos personas">
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                    <i class="fas fa-lock-open text-xs"></i> Reabrir para corregir
                                </button>
                            </form>
                            @endcanRoute
                        </div>
                    @endif

                    <div x-show="error" style="display:none;" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800" x-text="error"></div>

                    <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);" x-show="resultados.length > 0">
                        <div class="border-b px-4 py-2 text-sm font-extrabold" style="border-color:var(--border-color);color:var(--text-main);">Resultado de lo último que marcaste</div>
                        <ul class="max-h-80 space-y-1.5 overflow-y-auto p-3">
                            <template x-for="(item, i) in resultados" :key="i">
                                <li class="rounded-lg border px-3 py-2 text-xs" :class="clase(item.resultado)">
                                    <span class="font-mono font-bold" x-text="item.cedula"></span>
                                    <span class="font-semibold" x-show="item.nombre" x-text="' · ' + item.nombre"></span>
                                    <span class="block" x-text="item.mensaje"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-3 rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="border-b px-4 py-3" style="border-color:var(--border-color);">
                        <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Lista de presentes (<span x-text="estado.total_presentes">{{ $estado['total_presentes'] }}</span>)</h3>
                        <p class="text-xs" style="color:var(--text-muted);">Los marcados en amarillo asistieron sin estar {{ $actividad?->esTorneo() ? 'en un equipo del torneo' : 'inscritos' }}.</p>
                    </div>
                    <ul class="divide-y" style="border-color:var(--border-color);">
                        <template x-for="persona in estado.presentes" :key="persona.id">
                            <li class="flex items-center justify-between gap-3 px-4 py-2.5" :class="estado.usa_inscritos && !persona.inscrito ? 'bg-amber-50 dark:bg-amber-950/20' : ''">
                                <div>
                                    <p class="text-sm font-bold" style="color:var(--text-main);" x-text="persona.nombre"></p>
                                    <p class="text-xs" style="color:var(--text-muted);">
                                        <span class="font-mono" x-text="persona.cedula"></span>
                                        <span x-show="estado.usa_inscritos && !persona.inscrito" class="font-bold text-amber-700"> · No inscrito</span>
                                    </p>
                                </div>
                                <button type="button" x-show="!estado.cerrada && puedeQuitar" @click="quitar(persona.id, persona.nombre)" class="rounded-lg px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-50" title="Quitar de presentes">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        </template>
                        <li x-show="estado.presentes.length === 0" class="px-4 py-10 text-center text-sm" style="color:var(--text-muted);">
                            Todavía no has marcado a nadie. Escribe la primera cédula a la izquierda.
                        </li>
                    </ul>
                    <noscript>
                        <ul class="divide-y">
                            @foreach ($estado['presentes'] as $persona)
                                <li class="px-4 py-2 text-sm">{{ $persona['nombre'] }} · {{ $persona['cedula'] }}{{ $estado['usa_inscritos'] && ! $persona['inscrito'] ? ' · No inscrito' : '' }}</li>
                            @endforeach
                        </ul>
                    </noscript>
                </div>
            </div>
        </div>
    </div>
    @include('admin.deportes.partials.confirmar')
</x-app-layout>
