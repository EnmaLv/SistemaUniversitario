<x-app-layout>
    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10">

            @include('components.alert')

            @php
                $tieneReceta = (bool) $consulta->receta;
                $recetaCompleta = $tieneReceta && !$consulta->receta->tieneItemsPendientes();
                $pacienteNombre =
                    trim(
                        optional($consulta->paciente)->cedula_persona .
                            ' ' .
                            optional($consulta->paciente)->nombre_persona .
                            ' ' .
                            optional($consulta->paciente)->apellido_persona,
                    ) ?:
                    '—';
                $medicoNombre =
                    trim(
                        optional($consulta->medico)->nombre_persona .
                            ' ' .
                            optional($consulta->medico)->apellido_persona,
                    ) ?:
                    '—';
            @endphp

            {{-- Encabezado --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-sky-600 flex items-center justify-center text-white shadow-lg shadow-sky-600/20 shrink-0">
                        <i class="fas fa-file-medical text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Detalle de Atención
                        </h1>
                        <p
                            class="mt-0.5 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2 flex-wrap">
                            <span class="font-bold" style="color: var(--text-main);">Consulta #{{ $consulta->id }}</span>
                            <span class="text-gray-300 dark:text-gray-700">·</span>
                            <span>{{ $pacienteNombre }}</span>
                            <span class="text-gray-300 dark:text-gray-700">·</span>
                            <span>{{ optional($consulta->fecha)->translatedFormat('d \d\e F, Y') }}</span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all self-start md:self-auto shadow-sm"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver al listado
                </a>
            </div>

            <div x-data="{
                tab: 'step1',
                tieneReceta: @json($tieneReceta),
                recetaCompleta: @json($recetaCompleta)
            }">

                {{-- Navegación de Pestañas --}}
                <div class="rounded-2xl border shadow-sm p-1.5 mb-6"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="flex flex-col sm:flex-row gap-1.5">

                        {{-- Paso 1 --}}
                        <button type="button" @click="tab = 'step1'"
                            :class="tab === 'step1' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/25' :
                                'hover:bg-gray-50 dark:hover:bg-white/5'"
                            class="flex-1 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2.5"
                            :style="tab !== 'step1' ? 'color: var(--text-main);' : ''">
                            <i class="fas fa-check-circle text-xs"
                                :class="tab === 'step1' ? 'text-sky-200' : 'text-emerald-500'"></i>
                            1. Consulta Médica
                        </button>

                        {{-- Paso 2 --}}
                        <button type="button" @click="tieneReceta && (tab = 'step2')" :disabled="!tieneReceta"
                            :class="{
                                'bg-sky-600 text-white shadow-md shadow-sky-600/25': tab === 'step2',
                                'hover:bg-gray-50 dark:hover:bg-white/5': tab !== 'step2' && tieneReceta,
                                'opacity-40 cursor-not-allowed': !tieneReceta,
                            }"
                            class="flex-1 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2.5"
                            :style="tab !== 'step2' ? 'color: var(--text-main);' : ''">
                            <i class="fas text-xs"
                                :class="{
                                    'fa-check-circle text-sky-200': tieneReceta && tab === 'step2',
                                    'fa-check-circle text-emerald-500': tieneReceta && tab !== 'step2',
                                    'fa-lock text-gray-400': !tieneReceta,
                                }"></i>
                            2. Recetación
                        </button>

                        {{-- Paso 3 --}}
                        <button type="button" @click="tieneReceta && (tab = 'step3')" :disabled="!tieneReceta"
                            :class="{
                                'bg-sky-600 text-white shadow-md shadow-sky-600/25': tab === 'step3',
                                'hover:bg-gray-50 dark:hover:bg-white/5': tab !== 'step3' && tieneReceta,
                                'opacity-40 cursor-not-allowed': !tieneReceta,
                            }"
                            class="flex-1 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2.5"
                            :style="tab !== 'step3' ? 'color: var(--text-main);' : ''">
                            <i class="fas text-xs"
                                :class="{
                                    'fa-check-circle text-sky-200': recetaCompleta && tab === 'step3',
                                    'fa-check-circle text-emerald-500': recetaCompleta && tab !== 'step3',
                                    'fa-hand-holding-medical text-amber-500': tieneReceta && !recetaCompleta,
                                    'fa-lock text-gray-400': !tieneReceta,
                                }"></i>
                            3. Dispensación
                        </button>
                    </div>
                </div>

                {{-- ===================== PASO 1: CONSULTA ===================== --}}
                <div x-show="tab === 'step1'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0" class="rounded-3xl border shadow-sm"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- 1. Cabecera / Identificación (Grid sin bordes internos) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

                            {{-- Paciente --}}
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user text-sky-600 dark:text-sky-400 text-sm"></i>
                                </div>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Paciente</p>
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ $pacienteNombre }}
                                    </p>
                                </div>
                            </div>

                            {{-- Médico --}}
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-doctor text-sky-600 dark:text-sky-400 text-sm"></i>
                                </div>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Médico
                                        Atendiendo</p>
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ $medicoNombre }}
                                    </p>
                                </div>
                            </div>

                            {{-- Consultorio --}}
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                    <i class="fas fa-door-open text-sky-600 dark:text-sky-400 text-sm"></i>
                                </div>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Consultorio
                                    </p>
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ optional($consulta->consultorio)->nombre ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                    <i class="fas fa-virus text-sky-500 dark:text-sky-400 text-sm"></i>
                                </div>
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Enfermedades
                                        Previas</p>
                                    @if ($consulta->enfermedades->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($consulta->enfermedades as $enfermedad)
                                                {{ $enfermedad->nombre }}
                                                @if ($consulta->enfermedades->count() > 1)
                                                    <span> · </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm font-bold text-gray-400">—</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Separador --}}
                        <div class="h-px w-full" style="background-color: var(--border-color);"></div>

                        {{-- 2. Motivo y Observaciones --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {{-- Motivo --}}
                            <div class="space-y-3">
                                <h4 class="flex items-center gap-2 text-sm font-bold" style="color: var(--text-main);">
                                    <i class="fas fa-file-medical-alt text-sky-500"></i> Motivo de la Consulta
                                </h4>
                                <div class="p-5 rounded-2xl border shadow-inner"
                                    style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">
                                    <p class="text-sm leading-relaxed" style="color: var(--text-main);">
                                        {{ $consulta->motivo }}
                                    </p>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="space-y-3">
                                <h4 class="flex items-center gap-2 text-sm font-bold" style="color: var(--text-main);">
                                    <i class="fas fa-clipboard-list text-sky-400"></i> Observaciones
                                </h4>
                                <div class="p-5 rounded-2xl border shadow-inner"
                                    style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">
                                    <p class="text-sm leading-relaxed {{ $consulta->observaciones ? '' : 'italic text-gray-400' }}"
                                        style="{{ $consulta->observaciones ? 'color: var(--text-main);' : '' }}">
                                        {{ $consulta->observaciones ?: 'No se registraron observaciones adicionales durante la atención.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Separador --}}
                        <div class="h-px w-full" style="background-color: var(--border-color);"></div>

                        {{-- 3. Diagnóstico Final (Destacado) --}}
                        <div class="space-y-3">
                            <h4 class="flex items-center gap-2 text-sm font-bold" style="color: var(--text-main);">
                                <i class="fas fa-stethoscope text-sky-500 text-base"></i> Diagnóstico Médico Final
                            </h4>
                            <div class="p-5 md:p-6 rounded-2xl border-2 border-dashed shadow-sm"
                                style="background-color: rgba(16, 185, 129, 0.03); border-color: var(--border-color);">
                                <p class="text-sm md:text-base font-medium leading-relaxed"
                                    style="color: var(--text-main);">
                                    {{ $consulta->diagnostico }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ===================== PASO 2: RECETACIÓN ===================== --}}
                <div x-show="tab === 'step2'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0" class="rounded-3xl border shadow-sm"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <div class="p-4 sm:p-8 space-y-8">

                        @if ($consulta->receta)
                            {{-- Vigencia e Indicaciones --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 sm:p-4 rounded-2xl border shadow-inner"
                                style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">

                                {{-- Vigencia --}}
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                        <i class="fas fa-calendar-alt text-sky-600 dark:text-sky-400 text-sm"></i>
                                    </div>
                                    <div class="min-w-0 pt-0.5">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Vigencia
                                            hasta</p>
                                        <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                            {{ optional($consulta->receta->vigencia)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Indicaciones --}}
                                <div class="md:col-span-2 flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-sky-50 dark:bg-sky-950/40 flex items-center justify-center shrink-0">
                                        <i class="fas fa-info-circle text-sky-600 dark:text-sky-400 text-sm"></i>
                                    </div>
                                    <div class="min-w-0 pt-0.5 w-full">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">
                                            Indicaciones Generales</p>
                                        <p class="text-sm font-medium leading-relaxed"
                                            style="color: var(--text-main);">
                                            {{ $consulta->receta->descripcion ?: 'No se registraron indicaciones adicionales.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabla de Medicamentos --}}
                            <div class="space-y-3 pt-2">
                                <h4 class="flex items-center gap-2 text-sm font-bold"
                                    style="color: var(--text-main);">
                                    Lista de Medicamentos
                                </h4>

                                <div class="rounded-2xl border overflow-hidden"
                                    style="border-color: var(--border-color);">
                                    <table class="w-full text-left border-collapse">
                                        <thead style="background-color: rgba(0,0,0,0.02);">
                                            <tr class="text-xs font-semibold border-b"
                                                style="border-color: var(--border-color);">
                                                <th class="px-5 py-4">Medicamento</th>
                                                <th class="px-5 py-4">Cantidad</th>
                                                <th class="px-5 py-4">Frecuencia</th>
                                                <th class="px-5 py-4">Periodo de Uso</th>
                                                <th class="px-5 py-4">Estado Farmacia</th>
                                            </tr>
                                        </thead>
                                        <tbody style="border-color: var(--border-color);">
                                            @foreach ($consulta->receta->detalles as $detalle)
                                                <tr
                                                    class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                                    <td class="px-5 py-4 text-sm " style="color: var(--text-main);">
                                                        {{ optional($detalle->producto)->nombre ?? '—' }}
                                                    </td>
                                                    <td class="px-5 py-4 text-sm font-mono font-medium"
                                                        style="color: var(--text-main);">
                                                        {{ $detalle->cantidad }}
                                                        <span
                                                            class="text-gray-400 text-xs font-sans ">{{ optional($detalle->unidad)->nombre }}</span>
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                                        {{ $detalle->frecuencia }}
                                                    </td>
                                                    <td
                                                        class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/y') }}
                                                        <i
                                                            class="fas fa-arrow-right-long mx-1.5 text-gray-300 dark:text-gray-600 text-xs"></i>
                                                        {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/y') }}
                                                    </td>
                                                    <td class="px-5 py-4 text-center">
                                                        @if ($detalle->cantidad_pendiente <= 0)
                                                            <span
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-sm">
                                                                <i class="fas fa-box-open"></i> Entregado
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-sm">
                                                                <i class="fas fa-clock"></i> Pendiente
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ===================== PASO 3: DISPENSACIÓN ===================== --}}
                <div x-show="tab === 'step3'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0" class="rounded-3xl border shadow-sm"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <div class="p-6 sm:p-8 space-y-8">

                        @if ($consulta->receta)
                            @php
                                $hasDispensaciones = $consulta->receta->detalles
                                    ->pluck('dispensaciones')
                                    ->flatten()
                                    ->isNotEmpty();
                            @endphp

                            @if ($hasDispensaciones)
                                {{-- Tabla de Entregas --}}
                                <div class="space-y-3 pt-2">
                                    <h4 class="flex items-center gap-2 text-sm font-bold"
                                        style="color: var(--text-main);">
                                         Detalle de Entregas Registradas
                                    </h4>

                                    <div class="rounded-2xl border overflow-hidden"
                                        style="border-color: var(--border-color);">
                                        <table class="w-full text-left border-collapse">
                                            <thead style="background-color: rgba(0,0,0,0.02);">
                                                <tr class="text-xs font-semibold border-b"
                                                    style="border-color: var(--border-color);">
                                                    <th class="px-5 py-4">Fecha Entrega</th>
                                                    <th class="px-5 py-4">Producto</th>
                                                    <th class="px-5 py-4 text-center">Cantidad</th>
                                                    <th class="px-5 py-4">Lote</th>
                                                    <th class="px-5 py-4 text-right">Entregado por</th>
                                                </tr>
                                            </thead>
                                            <tbody style="border-color: var(--border-color);">
                                                @foreach ($consulta->receta->detalles as $detalle)
                                                    @foreach ($detalle->dispensaciones as $disp)
                                                        <tr
                                                            class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                                            <td class="px-5 py-4 text-sm "
                                                                style="color: var(--text-main);">
                                                                {{ optional($disp->fecha)->format('d/m/Y') }}
                                                            </td>
                                                            <td
                                                                class="px-5 py-4 text-sm font-semibold text-gray-600 dark:text-gray-300">
                                                                {{ optional($detalle->producto)->nombre ?? '—' }}
                                                            </td>
                                                            <td class="px-5 py-4 text-sm text-center"
                                                                style="color: var(--text-main);">
                                                                <span
                                                                    class="font-bold">{{ $disp->cantidad }}</span>
                                                                <span
                                                                    class="text-gray-400 text-xs font-sans ml-0.5">{{ optional($detalle->unidad)->nombre }}</span>
                                                            </td>
                                                            <td class="px-5 py-4 text-sm">
                                                                <div class="flex flex-col gap-0.5">
                                                                    <span class="font-medium"
                                                                        style="color: var(--text-main);">
                                                                        Lote:
                                                                        {{ optional($disp->lote)->numero_lote ?? (optional($disp->lote)->codigo_lote ?? '—') }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="px-5 py-4 text-right">
                                                                <div class="flex items-center justify-end gap-3">
                                                                    <span
                                                                        class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                                        {{ optional(optional($disp->usuario)->persona)->nombre_persona .' '. optional(optional($disp->usuario)->persona)->apellido_persona ?? (optional($disp->usuario)->username ?? '—') }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                {{-- Estado Vacío (Sin entregas) --}}
                                <div class="flex items-center gap-4 p-5 sm:p-6 rounded-2xl border shadow-inner"
                                    style="background-color: rgba(245,158,11,0.03); border-color: var(--border-color);">
                                    <div
                                        class="w-12 h-12 rounded-full bg-amber-100/50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                        <i class="fas fa-clock text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-0.5">
                                            Receta pendiente de dispensación
                                        </h4>
                                        <p class="text-sm font-medium text-amber-700/80 dark:text-amber-400/80">
                                            Aún no se han registrado entregas físicas de medicamentos para esta receta
                                            en el sistema de farmacia.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
