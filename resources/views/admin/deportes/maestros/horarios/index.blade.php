@php
    use Carbon\Carbon;

    $themeColor = 'green';

    $semanaActual = Carbon::now()->startOfWeek(Carbon::MONDAY);
    $semanaInicioActual = isset($semanaInicio) ? Carbon::parse($semanaInicio) : $semanaActual;
    $semanaFinActual = isset($semanaFin) ? Carbon::parse($semanaFin) : $semanaInicioActual->copy()->addDays(4);
    $esSemanaActual = $semanaInicioActual->equalTo($semanaActual);
    $DIAS = collect($diasSemana ?? [])->map(fn ($dia) => $dia['nombre'] ?? '')->values()->all();

    $BLOQUES = [
        'Matutino' => [
            ['inicio' => '07:30', 'fin' => '08:15'],
            ['inicio' => '08:15', 'fin' => '09:00'],
            ['inicio' => '09:20', 'fin' => '10:05'],
            ['inicio' => '10:05', 'fin' => '10:50'],
            ['inicio' => '10:50', 'fin' => '11:35'],
            ['inicio' => '11:35', 'fin' => '12:20'],
        ],
        'Vespertino' => [
            ['inicio' => '13:30', 'fin' => '14:15'],
            ['inicio' => '14:15', 'fin' => '15:00'],
            ['inicio' => '15:00', 'fin' => '15:45'],
            ['inicio' => '15:45', 'fin' => '16:30'],
            ['inicio' => '16:30', 'fin' => '17:15'],
            ['inicio' => '17:15', 'fin' => '18:00'],
        ],
        'Nocturno' => [
            ['inicio' => '18:00', 'fin' => '18:40'],
            ['inicio' => '18:40', 'fin' => '19:20'],
            ['inicio' => '19:20', 'fin' => '20:00'],
            ['inicio' => '20:00', 'fin' => '20:40'],
            ['inicio' => '20:40', 'fin' => '21:20'],
            ['inicio' => '21:20', 'fin' => '22:00'],
        ],
    ];

    $totalJornadas = count($BLOQUES);
@endphp

<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            @include('admin.deportes.partials.errores')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: var(--text-main);">
                        Horarios - Deportes
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestión semanal de horarios deportivos
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @canRoute('admin.deportes.maestros.horarios.create')
                    <a href="{{ route('admin.deportes.maestros.horarios.create', ['semana' => $semanaInicioActual->format('Y-m-d')]) }}"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-calendar-plus text-xs"></i>
                        <span>Crear Horario</span>
                    </a>
                    @endcanRoute

                    <a href="{{ route('admin.deportes.maestros.horarios.historial') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                        <i class="fas fa-history text-xs"></i>
                        <span>Historial</span>
                    </a>

                    @canRoute('admin.deportes.maestros.horarios.exportarPdf')
                    <a href="{{ route('admin.deportes.maestros.horarios.exportarPdf', ['semana' => $semanaInicioActual->format('Y-m-d')]) }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                        <i class="fas fa-file-pdf text-xs"></i>
                        <span>Exportar PDF</span>
                    </a>
                    @endcanRoute
                </div>
            </div>

            @php
                $semanaAnterior = $semanaInicioActual->copy()->subWeek();
                $semanaSiguiente = $semanaInicioActual->copy()->addWeek();
                $rangoSemana = $semanaInicioActual->format('d/m/Y') . ' - ' . $semanaFinActual->format('d/m/Y');
            @endphp

            <div class="mb-6 flex items-center justify-center gap-3">
                <a href="{{ $esSemanaActual ? '#' : route('admin.deportes.maestros.horarios.index', ['semana' => $semanaAnterior->format('Y-m-d')]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm transition {{ $esSemanaActual ? 'pointer-events-none cursor-not-allowed opacity-40' : 'hover:bg-gray-50' }} dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <i class="fas fa-chevron-left text-sm"></i>
                </a>

                <div class="min-w-[220px] text-center text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">
                    {{ $rangoSemana }}
                </div>

                <a href="{{ route('admin.deportes.maestros.horarios.index', ['semana' => $semanaSiguiente->format('Y-m-d')]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <i class="fas fa-chevron-right text-sm"></i>
                </a>
            </div>

            @foreach ($BLOQUES as $jornada => $bloques)
                @php $jornadaIndex = $loop->index; @endphp

                <div id="jornada-block-{{ $jornadaIndex }}"
                     class="jornada-block mb-8 {{ $jornadaIndex !== 0 ? 'hidden' : '' }}">

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
                        <div class="hidden sm:block sm:w-44"></div>

                        <div class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 dark:bg-gray-800/70 border border-gray-200/80 dark:border-gray-700/60 shadow-sm text-center">
                            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                Jornada {{ $jornada }}
                            </h3>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                             class="flex items-center justify-between md:justify-center gap-1 border border-gray-200 dark:border-gray-700/60 p-1 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full sm:w-auto">
                            <button type="button" onclick="cambiarJornada(-1)" title="Jornada Anterior"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <span class="px-2 sm:px-4 text-[10px] sm:text-[11px] font-black text-gray-800 dark:text-gray-200 min-w-[90px] text-center uppercase tracking-wider leading-none whitespace-nowrap">
                                {{ $loop->iteration }} / {{ $totalJornadas }}
                            </span>

                            <button type="button" onclick="cambiarJornada(1)" title="Siguiente Jornada"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @include('admin.deportes.maestros.horarios.partials._weekly_grid', [
                        'horarios' => $horarios,
                        'bloques' => $bloques,
                        'dias' => $DIAS,
                        'diasSemana' => $diasSemana,
                    ])
                </div>
            @endforeach
        </div>
    </div>

    <div id="detalleHorarioModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-800">Detalle del horario</h3>
                <button type="button" id="cerrarDetalleHorario" class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <div id="detalleHorarioContent" class="space-y-3 px-5 py-4 text-sm text-gray-700"></div>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 px-5 py-4">
                <a id="detalleVerLink" href="#" class="rounded-xl border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">Ver</a>
                @canRoute('admin.deportes.maestros.horarios.cartel_pdf')
                <a id="detalleCartelLink" href="#" target="_blank" class="rounded-xl border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50"><i class="fas fa-file-pdf mr-1"></i>Cartel</a>
                @endcanRoute
                @canRoute('admin.deportes.inscripciones.index')
                <a id="detalleInscritosLink" href="#" class="rounded-xl border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50"><i class="fas fa-user-plus mr-1"></i>Inscritos</a>
                @endcanRoute
                @canRoute('admin.deportes.maestros.horarios.edit')
                <a id="detalleEditarLink" href="#" class="rounded-xl bg-red-800 px-4 py-2 font-extrabold text-white hover:bg-red-900">Editar</a>
                @endcanRoute
                @canRoute('admin.deportes.maestros.horarios.destroy')
                <button type="button" id="detalleDeleteTrigger" class="rounded-xl border border-red-300 px-4 py-2 font-semibold text-red-700 hover:bg-red-50">Cancelar horario</button>
                @endcanRoute
                <button type="button" id="cerrarDetalleHorarioFooter" class="rounded-xl border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">Cerrar</button>
            </div>
        </div>
    </div>

    <div id="confirmCancelModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-800">Confirmar cancelación</h3>
                <button type="button" id="cerrarConfirmCancelModal" class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <form id="confirmCancelForm" method="POST" class="space-y-4 px-5 py-4">
                @csrf
                @method('DELETE')
                <div>
                    <label for="motivoCancelacion" class="mb-2 block text-sm font-semibold text-gray-700">Motivo de cancelación</label>
                    <textarea id="motivoCancelacion" name="motivo_cancelacion" rows="4" required minlength="5" maxlength="500" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200" placeholder="Ej. Se suspendió la sesión por mantenimiento del espacio."></textarea>
                    <p class="mt-2 text-xs text-gray-500">Este campo es obligatorio para confirmar la cancelación.</p>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" id="cancelConfirmCancel" class="rounded-xl border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">Confirmar cancelación</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let jornadaActual = 0;
        const totalJornadas = {{ $totalJornadas }};

        function cambiarJornada(direccion) {
            const bloqueActual = document.getElementById(`jornada-block-${jornadaActual}`);
            if (bloqueActual) bloqueActual.classList.add('hidden');

            jornadaActual = (jornadaActual + direccion + totalJornadas) % totalJornadas;

            const siguienteBloque = document.getElementById(`jornada-block-${jornadaActual}`);
            if (siguienteBloque) siguienteBloque.classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('detalleHorarioModal');
            const confirmModal = document.getElementById('confirmCancelModal');
            const content = document.getElementById('detalleHorarioContent');
            const editarLink = document.getElementById('detalleEditarLink');
            const verLink = document.getElementById('detalleVerLink');
            const cartelLink = document.getElementById('detalleCartelLink');
            const inscritosLink = document.getElementById('detalleInscritosLink');
            const deleteTrigger = document.getElementById('detalleDeleteTrigger');
            const confirmCancelForm = document.getElementById('confirmCancelForm');
            const motivoCancelacion = document.getElementById('motivoCancelacion');
            const closeButtons = [
                document.getElementById('cerrarDetalleHorario'),
                document.getElementById('cerrarDetalleHorarioFooter')
            ];
            const closeConfirmButtons = [
                document.getElementById('cerrarConfirmCancelModal'),
                document.getElementById('cancelConfirmCancel')
            ];

            let currentDeleteUrl = '';

            function openModal(data) {
                content.innerHTML = `
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div><strong>Actividad:</strong><br>${data.actividad}</div>
                        <div><strong>Calendario:</strong><br>${data.calendario}</div>
                        <div><strong>Espacio / Cancha:</strong><br>${data.espacio}</div>
                        <div><strong>Día:</strong><br>${data.dia}</div>
                        <div><strong>Horario:</strong><br>${data.horaInicio} - ${data.horaFin}</div>
                        <div><strong>Cupo máximo:</strong><br>${data.cupo}</div>
                        <div><strong>Tipo:</strong><br>${data.tipo}</div>
                        <div><strong>Inscritos:</strong><br>${data.inscritos}</div>
                        <div class="md:col-span-2"><strong>Vigencia:</strong><br>${data.vigencia || '-'}</div>
                        <div class="md:col-span-2"><strong>Observaciones:</strong><br>${data.observaciones || '-'}</div>
                    </div>
                `;

                if (editarLink) editarLink.href = data.editUrl;
                if (verLink) verLink.href = data.showUrl;
                if (cartelLink) cartelLink.href = data.cartelUrl;
                if (inscritosLink) inscritosLink.href = data.inscritosUrl;
                currentDeleteUrl = data.deleteUrl;
                confirmCancelForm.action = data.deleteUrl;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function openConfirmCancelModal() {
                motivoCancelacion.value = '';
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
                setTimeout(() => motivoCancelacion.focus(), 50);
            }

            function closeConfirmCancelModal() {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
                motivoCancelacion.value = '';
            }

            deleteTrigger?.addEventListener('click', function () {
                if (!currentDeleteUrl) return;
                closeModal();
                openConfirmCancelModal();
            });

            confirmCancelForm.addEventListener('submit', function (event) {
                const motivo = motivoCancelacion.value.trim();
                if (motivo.length < 5) {
                    event.preventDefault();
                    motivoCancelacion.focus();
                    motivoCancelacion.setCustomValidity('Debe ingresar un motivo con al menos 5 caracteres.');
                    motivoCancelacion.reportValidity();
                    return;
                }

                motivoCancelacion.setCustomValidity('');
            });

            document.querySelectorAll('.horario-card').forEach((button) => {
                button.addEventListener('click', function () {
                    openModal({
                        actividad: button.dataset.actividad,
                        calendario: button.dataset.calendario,
                        espacio: button.dataset.espacio,
                        dia: button.dataset.dia,
                        horaInicio: button.dataset.horaInicio,
                        horaFin: button.dataset.horaFin,
                        cupo: button.dataset.cupo,
                        tipo: button.dataset.tipo,
                        estado: button.dataset.estado,
                        inscritos: button.dataset.inscritos || '-',
                        vigencia: button.dataset.vigencia || '-',
                        showUrl: button.dataset.showUrl,
                        cartelUrl: button.dataset.cartelUrl,
                        inscritosUrl: button.dataset.inscritosUrl,
                        observaciones: button.dataset.observaciones || '-',
                        editUrl: button.dataset.editUrl,
                        deleteUrl: button.dataset.deleteUrl,
                    });
                });
            });

            closeButtons.forEach((button) => button.addEventListener('click', closeModal));
            closeConfirmButtons.forEach((button) => button.addEventListener('click', closeConfirmCancelModal));
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            confirmModal.addEventListener('click', function (event) {
                if (event.target === confirmModal) {
                    closeConfirmCancelModal();
                }
            });
        });
    </script>
</x-app-layout>
