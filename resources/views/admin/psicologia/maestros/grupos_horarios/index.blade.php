@php
    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);

    $themeColor = $esPsicologia ? 'indigo' : 'red';
    $btnClass = $esPsicologia ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700';
    $focusRingClass = $esPsicologia
        ? 'focus:ring-indigo-500/20 focus:border-indigo-500'
        : 'focus:ring-red-500/20 focus:border-red-500';
@endphp

<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            @if (session('error'))
                <div
                    class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong
                            class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Encabezado -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Grupos de Horarios
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestiona las agrupaciones y la disponibilidad de horarios para <strong
                            class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.psicologia.maestros.horarios.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all shadow-xs">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Volver a Horarios</span>
                    </a>
                </div>
            </div>

            @if (!empty($tieneCitasPendientes))
                <div
                    class="p-4 mb-6 text-xs sm:text-sm text-amber-800 dark:text-amber-400 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center gap-3 shadow-xs">
                    <i class="fas fa-lock text-amber-600 dark:text-amber-400 text-base shrink-0"></i>
                    <span><strong>Acción Restringida:</strong> No puedes modificar ni cambiar de estado los grupos de
                        horarios mientras existan citas pendientes o confirmadas.</span>
                </div>
            @endif

            <!-- Grid de Grupos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($grupos as $grupo)
                    @php
                        $esActivo = $grupo->activo == \App\Models\salud\GrupoHorario::STATUS_ACTIVE;
                    @endphp

                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="p-6 rounded-2xl border shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full relative group {{ $esActivo ? 'ring-2 ring-' . $themeColor . '-500/30' : '' }}">

                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-bold tracking-tight uppercase leading-tight truncate"
                                    style="color: var(--text-main);">
                                    {{ $grupo->nombre }}
                                </h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mt-1">
                                    <i class="fas fa-clock me-1 text-gray-400"></i> {{ $grupo->horarios->count() }}
                                    bloques
                                </span>
                            </div>

                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shrink-0 {{ $esActivo ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700' }}">
                                <i
                                    class="fas {{ $esActivo ? 'fa-check-circle' : 'fa-pause-circle' }} me-1 text-[9px]"></i>
                                {{ $esActivo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        <div class="flex-grow"></div>

                        <!-- Acciones -->
                        <div
                            class="flex items-center justify-between gap-2 pt-4 border-t border-gray-100 dark:border-gray-800/80 mt-4">
                            <div>
                                @if (!$esActivo)
                                    @if (!empty($tieneCitasPendientes))
                                        <button type="button" disabled title="Bloqueado por citas pendientes"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-700 cursor-not-allowed">
                                            <i class="fas fa-power-off text-[10px]"></i> Activar
                                        </button>
                                    @else
                                        <form
                                            action="{{ route('admin.psicologia.maestros.grupos_horarios.activate', $grupo->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition-all shadow-xs">
                                                <i class="fas fa-power-off text-[10px]"></i> Activar
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if (!empty($tieneCitasPendientes))
                                        <button type="button" disabled title="Bloqueado por citas pendientes"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-700 cursor-not-allowed">
                                            <i class="fas fa-ban text-[10px]"></i> Desactivar
                                        </button>
                                    @else
                                        <form
                                            action="{{ route('admin.psicologia.maestros.grupos_horarios.deactivate', $grupo->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all shadow-xs">
                                                <i class="fas fa-ban text-[10px]"></i> Desactivar
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5">
                                <!-- Ver Modal -->
                                <button type="button" 
                                    onclick="openGrupoModal(this)"
                                    class="p-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-{{ $themeColor }}-600 hover:text-white transition-all text-xs font-bold flex items-center justify-center"
                                    title="Ver detalle del grupo">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Editar -->
                                @if (!empty($tieneCitasPendientes))
                                    <span
                                        class="p-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 rounded-xl text-xs font-bold flex items-center justify-center cursor-not-allowed"
                                        title="Bloqueado por citas pendientes">
                                        <i class="fas fa-pen"></i>
                                    </span>
                                @else
                                    <a href="{{ route('admin.psicologia.maestros.horarios.index', ['grupo' => $grupo->id]) }}"
                                        class="p-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-{{ $themeColor }}-600 hover:text-white transition-all text-xs font-bold flex items-center justify-center"
                                        title="Editar bloques de este grupo">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endif

                                <!-- Eliminar -->
                                @if (!empty($tieneCitasPendientes))
                                    <span
                                        class="p-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 rounded-xl text-xs font-bold flex items-center justify-center cursor-not-allowed"
                                        title="Bloqueado por citas pendientes">
                                        <i class="fas fa-trash-alt"></i>
                                    </span>
                                @else
                                    <form
                                        action="{{ route('admin.psicologia.maestros.grupos_horarios.destroy', $grupo->id) }}"
                                        method="POST" data-ajax-reload="true"
                                        data-confirm-message="¿Estás seguro de eliminar este grupo de horarios?"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 rounded-xl hover:bg-rose-600 hover:text-white transition-all text-xs font-bold flex items-center justify-center"
                                            title="Eliminar grupo">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Modal de Detalle -->
                        <div id="grupoModal-{{ $grupo->id }}"
                            class="modal-container fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="rounded-2xl border shadow-2xl w-full max-w-5xl p-6 overflow-y-auto max-h-[90vh] transition-all">

                                <div
                                    class="flex justify-between items-center pb-4 mb-6 border-b border-gray-100 dark:border-gray-800">
                                    <div>
                                        <h3 class="text-xl font-extrabold tracking-tight"
                                            style="color: var(--text-main);">
                                            Grupo: {{ $grupo->nombre }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Distribución de bloques
                                            horarios por día de la semana.</p>
                                    </div>
                                    <button onclick="closeGrupoModal('grupoModal-{{ $grupo->id }}')"
                                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg rounded-xl transition-all">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                    @php
                                        $dias = \App\Models\salud\Horario::diasSemana();
                                        $horariosPorDia = [];
                                        foreach ($dias as $dia) {
                                            $horariosPorDia[$dia] = $grupo->horarios
                                                ->where('dia', $dia)
                                                ->sortBy('hora_inicio');
                                        }
                                    @endphp

                                    @foreach ($dias as $dia)
                                        <div
                                            class="bg-gray-50 dark:bg-gray-800/40 rounded-xl p-3 border border-gray-200/60 dark:border-gray-700/60 flex flex-col">
                                            <h4
                                                class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 text-center">
                                                {{ $dia }}
                                            </h4>
                                            @if ($horariosPorDia[$dia]->isEmpty())
                                                <p
                                                    class="text-[11px] text-gray-400 dark:text-gray-500 text-center my-auto py-4 font-medium italic">
                                                    Sin bloques
                                                </p>
                                            @else
                                                <div class="space-y-2">
                                                    @foreach ($horariosPorDia[$dia] as $horario)
                                                        <div
                                                            class="bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-center shadow-2xs {{ $horario->activo == \App\Models\salud\Horario::STATUS_INACTIVE ? 'opacity-50' : '' }}">
                                                            <span
                                                                class="text-xs font-bold text-gray-800 dark:text-gray-200 block">
                                                                {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}
                                                            </span>
                                                            @if ($horario->descripcion)
                                                                <p
                                                                    class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                                                    {{ $horario->descripcion }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                                    <button onclick="closeGrupoModal('grupoModal-{{ $grupo->id }}')"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                                        Cerrar Vista
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full">
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm">
                            <div
                                class="w-16 h-16 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3 class="text-lg font-bold mb-1" style="color: var(--text-main);">
                                Sin grupos de horarios configurados
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
                                Crea tu horario para gestionarlo. Una vez que definas tus bloques de atención, podrás
                                organizarlos por grupos aquí.
                            </p>
                            <a href="{{ route('admin.psicologia.maestros.horarios.index') }}"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white font-bold text-xs shadow-md active:scale-95 transition-all">
                                <i class="fas fa-plus text-xs"></i>
                                <span>Gestionar Bloques</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        function openGrupoModal(button) {
            var targetId = button.getAttribute('data-target');
            var modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeGrupoModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }

        function handleAjaxReloadForm(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (form.dataset.ajaxReload !== 'true') return;

                var doSubmit = function() {
                    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!token) {
                        if (window.AppModal) {
                            AppModal.alert('Error',
                                'No se pudo obtener CSRF token. Recarga la página e inténtalo de nuevo.');
                        } else {
                            alert('No se pudo obtener el token CSRF.');
                        }
                        return;
                    }

                    var formData = new FormData(form);
                    if (form.method.toUpperCase() === 'POST' && form.querySelector('input[name="_method"]')) {
                        formData.set('_method', form.querySelector('input[name="_method"]').value);
                    }

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    }).then(function(response) {
                        if (!response.ok) {
                            return response.json().then(function(body) {
                                throw new Error(body.message ||
                                    'No se pudo completar la acción.');
                            });
                        }
                        return response.json();
                    }).then(function(result) {
                        if (result && result.status === 'success') {
                            window.location.reload();
                        } else {
                            throw new Error(result.message || 'No se pudo completar la acción.');
                        }
                    }).catch(function(error) {
                        console.error('Error en acción de horario:', error);
                        if (window.AppModal) {
                            AppModal.alert('Error', error.message ||
                                'Error al procesar la acción. Recarga la página e inténtalo nuevamente.'
                                );
                        } else {
                            alert(error.message || 'Error al procesar la acción.');
                        }
                    });
                };

                if (form.dataset.confirmMessage) {
                    if (window.AppModal) {
                        AppModal.confirm('Confirmar', form.dataset.confirmMessage).then(function(confirmed) {
                            if (confirmed) doSubmit();
                        });
                    } else {
                        if (confirm(form.dataset.confirmMessage)) doSubmit();
                    }
                } else {
                    doSubmit();
                }
            });
        }

        document.querySelectorAll('form[data-ajax-reload="true"]').forEach(handleAjaxReloadForm);

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (target.classList.contains('modal-container')) {
                target.classList.remove('flex');
                target.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
