<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Grupo: ') . $grupoHorario->nombre_gha }}
            </h2>
            <div class="flex space-x-2">
                @canRoute('admin.psicologia.maestros.grupos_horarios.edit')
                @if(!empty($tieneCitasPendientes))
                    <button type="button" disabled class="bg-blue-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes editar mientras tengas citas pendientes">
                        Editar Grupo
                    </button>
                @else
                    <a href="{{ route('admin.psicologia.maestros.grupos_horarios.edit', $grupoHorario->id_gha) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        Editar Grupo
                    </a>
                @endif
                @endcanRoute
                <a href="{{ route('admin.psicologia.maestros.grupos_horarios.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl border-t-8 border-blue-600 overflow-hidden">
                <div class="p-6">
                    {{-- ================================Estado del Grupo================================== --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Estado del Grupo</h3>
                        <span class="px-3 py-1 rounded {{ $grupoHorario->status_id == \App\Models\salud\GrupoHorario::STATUS_ACTIVE ? 'bg-green-200 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                            {{ $grupoHorario->status_id == \App\Models\salud\GrupoHorario::STATUS_ACTIVE ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    {{-- ================================Calendario Semanal================================== --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach ($horariosPorDia as $dia => $horariosDia)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 text-center">{{ $dia }}</h4>
                                @if ($horariosDia->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Sin bloques</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($horariosDia as $horario)
                                            <div class="relative bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 text-center {{ $horario->status_id == \App\Models\salud\Horario::STATUS_INACTIVE ? 'opacity-50' : '' }}">
                                                <div class="mb-2">
                                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $horario->hora_inicio_hpr }} - {{ $horario->hora_fin_hpr }}</span>
                                                </div>
                                                @if($horario->descripcion_hpr)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $horario->descripcion_hpr }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- ================================Acciones================================== --}}
                    <div class="mt-6 flex space-x-4">
                        @if($grupoHorario->status_id != \App\Models\salud\GrupoHorario::STATUS_ACTIVE)
                            @canRoute('admin.psicologia.maestros.grupos_horarios.activate')
                            @if(!empty($tieneCitasPendientes))
                                <button type="button" disabled class="bg-green-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes activar mientras tengas citas pendientes">
                                    Activar Grupo
                                </button>
                            @else
                                <form action="{{ route('admin.psicologia.maestros.grupos_horarios.activate', $grupoHorario->id_gha) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                                        Activar Grupo
                                    </button>
                                </form>
                            @endif
                            @endcanRoute
                        @else
                            @canRoute('admin.psicologia.maestros.grupos_horarios.deactivate')
                            @if(!empty($tieneCitasPendientes))
                                <button type="button" disabled class="bg-yellow-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes desactivar mientras tengas citas pendientes">
                                    Desactivar Grupo
                                </button>
                            @else
                                <form action="{{ route('admin.psicologia.maestros.grupos_horarios.deactivate', $grupoHorario->id_gha) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition">
                                        Desactivar Grupo
                                    </button>
                                </form>
                            @endif
                            @endcanRoute
                        @endif

                        @canRoute('admin.psicologia.maestros.grupos_horarios.destroy')
                        @if(!empty($tieneCitasPendientes))
                            <button type="button" disabled class="bg-red-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" title="No puedes eliminar mientras tengas citas pendientes">
                                Eliminar Grupo
                            </button>
                        @else
                            <form action="{{ route('admin.psicologia.maestros.grupos_horarios.destroy', $grupoHorario->id_gha) }}" method="POST" data-ajax-reload="true" class="inline" data-confirm-message="¿Estás seguro de eliminar este grupo?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                                    Eliminar Grupo
                                </button>
                            </form>
                        @endif
                        @endcanRoute
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleAjaxReloadForm(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (form.dataset.ajaxReload !== 'true') {
                    return;
                }

                var doSubmit = function() {
                    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!token) {
                        AppModal.alert('Error', 'No se pudo obtener CSRF token. Recarga la página e inténtalo de nuevo.');
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
                                throw new Error(body.message || 'No se pudo completar la acción.');
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
                        AppModal.alert('Error', error.message || 'Error al procesar la acción. Recarga la página e inténtalo nuevamente.');
                    });
                };

                if (form.dataset.confirmMessage) {
                    AppModal.confirm('Confirmar', form.dataset.confirmMessage).then(function(confirmed) {
                        if (confirmed) doSubmit();
                    });
                } else {
                    doSubmit();
                }
            });
        }

        document.querySelectorAll('form[data-ajax-reload="true"]').forEach(handleAjaxReloadForm);
    </script>
</x-app-layout>
