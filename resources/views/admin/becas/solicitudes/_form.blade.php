@php
    $modo = $modo ?? 'admin';
    $esEstudiante = $modo === 'estudiante';
@endphp

<div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.alert')

        <div class="mb-6">
            <a href="{{ route('admin.becas.solicitudes.index') }}"
                class="inline-flex items-center text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-widest hover:text-red-800 transition-colors mb-2">
                <i class="fas fa-arrow-left mr-2"></i>
                {{ $esEstudiante ? 'Volver a mis solicitudes' : 'Volver a solicitudes' }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                {{ $esEstudiante ? 'Postulación a Beneficio Estudiantil' : 'Registrar Solicitud de Beca' }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $esEstudiante
    ? 'Completa tu expediente para registrar tu solicitud.'
    : 'Completa el expediente del estudiante para registrar su postulación.' }}
            </p>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm overflow-hidden mb-6">

            {{-- INDICADOR DE PASOS --}}
            <div class="border-b px-8 py-5"
                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                <div class="flex items-center justify-between max-w-xl mx-auto">
                    <button type="button" onclick="goToStep(1)"
                        class="flex flex-col items-center text-center gap-2 focus:outline-none">
                        <span id="step-circle-1"
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-red-700 border-red-700 text-white shadow-md">1</span>
                        <span id="step-text-1"
                            class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Académico</span>
                    </button>
                    <div class="flex-1 h-0.5 mx-4 mb-6" style="background-color: var(--border-color);"></div>
                    <button type="button" onclick="goToStep(2)"
                        class="flex flex-col items-center text-center gap-2 focus:outline-none">
                        <span id="step-circle-2"
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700">2</span>
                        <span id="step-text-2"
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Formulario</span>
                    </button>
                    <div class="flex-1 h-0.5 mx-4 mb-6" style="background-color: var(--border-color);"></div>
                    <button type="button" onclick="goToStep(3)"
                        class="flex flex-col items-center text-center gap-2 focus:outline-none">
                        <span id="step-circle-3"
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700">3</span>
                        <span id="step-text-3"
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Confirmar</span>
                    </button>
                </div>
            </div>

            <form id="solicitudForm" action="{{ $action }}" method="POST" enctype="multipart/form-data"
            class="p-6 sm:p-8" novalidate>
                @csrf

                {{-- PASO 1: DATOS BASE --}}
                <div id="step-content-1" class="step-pane">
                    <h4 class="text-lg font-black uppercase tracking-tight mb-6 flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-user-graduate text-red-700"></i> Datos del solicitante
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                        @if ($esEstudiante)
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                    Estudiante solicitante
                                </label>
                                <div class="rounded-xl border p-3 flex items-center gap-3"
                                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                                    <div
                                        class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 flex items-center justify-center font-bold text-lg">
                                        {{ substr(auth()->user()->persona?->nombre_persona ?? 'E', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold" style="color: var(--text-main);">
                                            {{ auth()->user()->persona?->nombre_persona }}
                                            {{ auth()->user()->persona?->apellido_persona }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                            CI: {{ auth()->user()->persona?->cedula_persona ?? 'No registrada' }}
                                        </p>
                                    </div>
                                </div>
                                <input type="hidden" name="id_persona" value="{{ auth()->user()->id_persona }}">
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                    Estudiante *
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-user text-sm"></i>
                                    </span>
                                    <select name="id_persona" id="id_persona" required
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                        <option value="" disabled {{ old('id_persona') ? '' : 'selected' }}>
                                            Seleccione al estudiante...
                                        </option>
                                        @foreach ($estudiantes as $est)
                                            <option value="{{ $est->id_persona }}"
                                                @selected(old('id_persona') == $est->id_persona)>
                                                {{ $est->nombre_persona }} {{ $est->apellido_persona }}
                                                — {{ $est->cedula_persona }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('id_persona')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                Jornada activa *
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-bullhorn text-sm"></i>
                                </span>
                                <select name="jornada_id" id="jornada_id" required onchange="actualizarJornada()"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                    <option value="" disabled {{ old('jornada_id') ? '' : 'selected' }}>
                                        Seleccione la jornada...
                                    </option>
                                    @foreach ($jornadas as $j)
                                        <option value="{{ $j->id }}" data-beneficio-id="{{ $j->beneficio_id }}"
                                            data-lapso-id="{{ $j->lapsos_id }}" @selected(old('jornada_id') == $j->id)>
                                            {{ $j->nombre_jornada }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('jornada_id')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                Beneficio
                            </label>
                            <input type="text" id="beneficio_nombre_disabled" readonly
                                placeholder="Se autocompleta al elegir jornada"
                                style="background-color: rgba(0,0,0,0.05); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full text-sm px-4 py-2.5 rounded-xl border outline-none cursor-not-allowed">
                            <input type="hidden" name="id_beneficio" id="id_beneficio"
                                value="{{ old('id_beneficio') }}">
                            @error('id_beneficio')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                Lapso académico
                            </label>
                            <input type="text" id="lapso_codigo_disabled" readonly
                                placeholder="Se autocompleta al elegir jornada"
                                style="background-color: rgba(0,0,0,0.05); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full text-sm px-4 py-2.5 rounded-xl border outline-none cursor-not-allowed">
                            <input type="hidden" name="id_lapso" id="id_lapso" value="{{ old('id_lapso') }}">
                            @error('id_lapso')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                Tipo de solicitud *
                            </label>
                            <select name="tipo_solicitud" required
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full text-sm px-4 py-2.5 rounded-xl border focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="nueva" {{ old('tipo_solicitud') === 'nueva' ? 'selected' : '' }}>
                                    Nueva
                                </option>
                                <option value="renovacion" {{ old('tipo_solicitud') === 'renovacion' ? 'selected' : '' }}>
                                    Renovación
                                </option>
                            </select>
                            @error('tipo_solicitud')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Archivo de Notas (PDF) *
                        </label>

                        <div class="flex flex-col md:flex-row gap-6 items-start">
                            <div class="w-full md:w-5/12 lg:w-2/5">
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-file-pdf text-sm text-red-500"></i>
                                    </span>
                                    <input type="file" name="archivo_notas" accept="application/pdf" required
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-900/30 dark:file:text-red-400 dark:hover:file:bg-red-900/50 transition-all cursor-pointer">
                                </div>
                                @error('archivo_notas')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $esEstudiante ? 'Sube tu archivo PDF de notas para revisión.' : 'Sube el archivo PDF de notas del estudiante.' }}
                                </p>
                            </div>

                            <div class="w-full md:w-7/12 lg:w-3/5">
                                <div
                                    class="rounded-xl border p-4 bg-sky-50/50 border-sky-100 dark:bg-sky-950/20 dark:border-sky-900/50">
                                    <h5
                                        class="text-[11px] font-black text-sky-800 dark:text-sky-300 uppercase tracking-wider mb-2">
                                        <i class="fas fa-info-circle mr-1"></i> Origen del Documento
                                    </h5>
                                    <p class="text-[11px] text-sky-700 dark:text-sky-400 leading-relaxed font-medium">
                                        Este documento oficial lo puedes descargar directamente desde el sistema <span
                                            class="font-bold">SOGAC</span> de la universidad. Asegúrate de adjuntar el
                                        archivo original sin alteraciones.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="button" onclick="goToStep(2)"
                            class="px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            Siguiente <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- PASO 2: FORMULARIO DINÁMICO --}}
                <div id="step-content-2" class="step-pane hidden">
                    <h4 class="text-lg font-black uppercase tracking-tight mb-6 flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-clipboard-list text-red-700"></i> Formulario de postulación
                    </h4>

                    <div id="avisoSinJornada" class="rounded-2xl border p-8 text-center"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <i class="fas fa-arrow-left text-2xl text-gray-300 dark:text-gray-700 mb-2 block"></i>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                            Selecciona una jornada en el paso anterior para cargar el formulario
                        </p>
                    </div>

                    <div id="preguntasContainer" class="hidden space-y-4"></div>

                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="goToStep(1)"
                            class="px-6 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="fas fa-arrow-left mr-2 text-xs"></i> Anterior
                        </button>
                        <button type="button" onclick="goToStep(3)"
                            class="px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            Siguiente <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- PASO 3: CONFIRMACIÓN --}}
                <div id="step-content-3" class="step-pane hidden">
                    <h4 class="text-lg font-black uppercase tracking-tight mb-6 flex items-center gap-2"
                        style="color: var(--text-main);">
                        <i class="fas fa-check-double text-red-700"></i> Resumen y confirmación
                    </h4>

                    <div class="rounded-2xl border p-6 mb-6"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Verifica que toda la información esté correcta antes de enviar tu solicitud.
                        </p>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-[10px] font-black uppercase tracking-wider text-gray-400">Jornada</dt>
                                <dd id="resumen-jornada" class="font-bold" style="color: var(--text-main);">—</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-black uppercase tracking-wider text-gray-400">Beneficio</dt>
                                <dd id="resumen-beneficio" class="font-bold" style="color: var(--text-main);">—</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-black uppercase tracking-wider text-gray-400">Lapso</dt>
                                <dd id="resumen-lapso" class="font-bold" style="color: var(--text-main);">—</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-black uppercase tracking-wider text-gray-400">Tipo</dt>
                                <dd id="resumen-tipo" class="font-bold" style="color: var(--text-main);">—</dd>
                            </div>
                        </dl>
                    </div>

                    <div id="resumenRespuestas" class="rounded-2xl border p-6 mb-6"
                        style="border-color: var(--border-color);">
                        <h5 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">
                            Respuestas registradas
                        </h5>
                        <ul id="resumenRespuestasList" class="space-y-2 text-sm"></ul>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="goToStep(2)"
                            class="px-6 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="fas fa-arrow-left mr-2 text-xs"></i> Anterior
                        </button>
                        <button type="submit"
                            class="rd-submit-btn inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            <i class="fas fa-paper-plane text-xs"></i> Enviar solicitud
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.FORMULARIOS_POR_JORNADA = @json($formularios);
</script>

<script>
    let currentStep = 1;

    function goToStep(step) {
        if (step > currentStep) {
            // Validar paso actual antes de avanzar
            const currentPane = document.getElementById(`step-content-${currentStep}`);
            const inputs = currentPane.querySelectorAll('input, select, textarea');
            let isValid = true;
            for (let i = 0; i < inputs.length; i++) {
                if (!inputs[i].checkValidity()) {
                    inputs[i].reportValidity();
                    isValid = false;
                    break;
                }
            }
            if (!isValid) return; // Bloquear avance
        }

        document.querySelectorAll('.step-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(`step-content-${step}`).classList.remove('hidden');

        for (let i = 1; i <= 3; i++) {
            const circle = document.getElementById(`step-circle-${i}`);
            const text = document.getElementById(`step-text-${i}`);

            if (i < step) {
                circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-emerald-50 border-emerald-500 text-emerald-500 dark:bg-emerald-950/20";
                circle.innerHTML = '<i class="fas fa-check text-xs"></i>';
                text.className = "text-xs font-bold text-emerald-500 uppercase tracking-wider";
            } else if (i === step) {
                circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-red-700 border-red-700 text-white shadow-md";
                circle.innerHTML = i;
                text.className = "text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider";
            } else {
                circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700";
                circle.innerHTML = i;
                text.className = "text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider";
            }
        }

        if (step === 3) construirResumen();
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function actualizarJornada() {
        const select = document.getElementById('jornada_id');
        const opt = select.options[select.selectedIndex];

        const beneficioId = opt.getAttribute('data-beneficio-id') || '';
        const lapsoId = opt.getAttribute('data-lapso-id') || '';

        document.getElementById('id_beneficio').value = beneficioId;
        document.getElementById('id_lapso').value = lapsoId;

        const data = window.FORMULARIOS_POR_JORNADA[select.value];
        if (data) {
            document.getElementById('beneficio_nombre_disabled').value = opt.text.split('·')[0].trim() || '';
            document.getElementById('lapso_codigo_disabled').value = '';
        }

        renderPreguntas(select.value);
    }

    function renderPreguntas(jornadaId) {
        const container = document.getElementById('preguntasContainer');
        const aviso = document.getElementById('avisoSinJornada');

        container.innerHTML = '';

        if (!jornadaId || !window.FORMULARIOS_POR_JORNADA[jornadaId]) {
            container.classList.add('hidden');
            aviso.classList.remove('hidden');
            return;
        }

        const preguntas = window.FORMULARIOS_POR_JORNADA[jornadaId].preguntas || [];

        if (preguntas.length === 0) {
            container.classList.add('hidden');
            aviso.classList.remove('hidden');
            aviso.querySelector('p').textContent = 'Este beneficio no tiene preguntas configuradas.';
            return;
        }

        aviso.classList.add('hidden');
        container.classList.remove('hidden');

        preguntas.forEach((p, i) => container.appendChild(buildPregunta(p, i)));
    }

    function buildPregunta(p, idx) {
        const wrap = document.createElement('div');
        wrap.className = 'rounded-2xl border p-4 sm:p-5';
        wrap.style.borderColor = 'var(--border-color)';
        wrap.style.backgroundColor = 'rgba(0,0,0,0.015)';

        const badgeElim = p.criterio && p.criterio.es_eliminatoria
            ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 ml-2 text-[10px] font-black rounded-md bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900">
                   <i class="fas fa-shield-alt text-[8px]"></i> Eliminatorio
               </span>`
            : '';

        const req = p.obligatoria
            ? `<span class="text-red-600">*</span>`
            : `<span class="text-gray-400 text-[10px]">(opcional)</span>`;

        let inputHtml = '';
        const nameValor = `respuestas[${idx}][valor]`;
        const nameValorJson = `respuestas[${idx}][valor_json][]`;
        const hiddenPregunta = `<input type="hidden" name="respuestas[${idx}][id_pregunta]" value="${p.id}">`;

        const tipo = p.tipo;

        if (tipo === 'textarea') {
            inputHtml = `<textarea name="${nameValor}" rows="3" ${p.obligatoria ? 'required' : ''}
                            placeholder="${p.placeholder ?? ''}"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full text-sm px-4 py-2.5 rounded-xl border focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>`;
        } else if (tipo === 'select') {
            const opts = (p.opciones || []).map(o =>
                `<option value="${o.valor}">${o.etiqueta}</option>`).join('');
            inputHtml = `<select name="${nameValor}" ${p.obligatoria ? 'required' : ''}
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full text-sm px-4 py-2.5 rounded-xl border focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="">Seleccione...</option>${opts}
                        </select>`;
        } else if (tipo === 'radio') {
            inputHtml = `<div class="flex flex-wrap gap-3">${(p.opciones || []).map(o => `
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer"
                        style="border-color: var(--border-color);">
                        <input type="radio" name="${nameValor}" value="${o.valor}" ${p.obligatoria ? 'required' : ''}
                            class="text-red-700 focus:ring-red-500">
                        <span class="text-sm font-medium" style="color: var(--text-main);">${o.etiqueta}</span>
                    </label>`).join('')
                }</div>`;
        } else if (tipo === 'checkbox') {
            inputHtml = `<div class="flex flex-wrap gap-3">${(p.opciones || []).map(o => `
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer"
                        style="border-color: var(--border-color);">
                        <input type="checkbox" name="${nameValorJson}" value="${o.valor}"
                            class="rounded text-red-700 focus:ring-red-500">
                        <span class="text-sm font-medium" style="color: var(--text-main);">${o.etiqueta}</span>
                    </label>`).join('')
                }</div>`;
        } else if (tipo === 'boolean') {
            inputHtml = `<div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="${nameValor}" value="1" ${p.obligatoria ? 'required' : ''}
                        class="text-red-700 focus:ring-red-500">
                    <span class="text-sm font-medium" style="color: var(--text-main);">Sí</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="${nameValor}" value="0" ${p.obligatoria ? 'required' : ''}
                        class="text-red-700 focus:ring-red-500">
                    <span class="text-sm font-medium" style="color: var(--text-main);">No</span>
                </label>
            </div>`;
        } else {
            const inputType = tipo === 'email' ? 'email'
                : tipo === 'date' ? 'date'
                    : tipo === 'number' ? 'number'
                        : tipo === 'decimal' ? 'number' : 'text';

            const step = tipo === 'decimal' ? '0.01' : '1';
            const min = p.valor_min !== null ? `min="${p.valor_min}"` : '';
            const max = p.valor_max !== null ? `max="${p.valor_max}"` : '';
            const minL = p.min_length ? `minlength="${p.min_length}"` : '';
            const maxL = p.max_length ? `maxlength="${p.max_length}"` : '';
            const pat = p.regex ? `pattern="${p.regex.replace(/^\/|\/$/g, '')}"` : '';

            inputHtml = `<input type="${inputType}" name="${nameValor}" ${p.obligatoria ? 'required' : ''}
                            placeholder="${p.placeholder ?? ''}"
                            ${inputType === 'number' ? `step="${step}" ${min} ${max}` : ''}
                            ${minL} ${maxL} ${pat}
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full text-sm px-4 py-2.5 rounded-xl border focus:ring-2 focus:ring-sky-500 focus:outline-none">`;
        }

        wrap.innerHTML = `
            ${hiddenPregunta}
            <div class="mb-2 flex flex-wrap items-center gap-1">
                <label class="text-sm font-bold" style="color: var(--text-main);">
                    ${p.etiqueta} ${req}
                </label>
                ${badgeElim}
            </div>
            ${inputHtml}
        `;

        return wrap;
    }

    function construirResumen() {
        const selectJ = document.getElementById('jornada_id');
        const opt = selectJ.options[selectJ.selectedIndex];

        document.getElementById('resumen-jornada').textContent = opt?.text ?? '—';
        document.getElementById('resumen-beneficio').textContent = document.getElementById('beneficio_nombre_disabled').value || '—';
        document.getElementById('resumen-lapso').textContent = document.getElementById('lapso_codigo_disabled').value || '—';
        document.getElementById('resumen-tipo').textContent = document.querySelector('select[name="tipo_solicitud"]').selectedOptions[0].text;

        const list = document.getElementById('resumenRespuestasList');
        list.innerHTML = '';

        document.querySelectorAll('#preguntasContainer > div').forEach(block => {
            const label = block.querySelector('label')?.textContent.trim() ?? '—';
            const valor = leerValorBloque(block);

            const li = document.createElement('li');
            li.className = 'flex items-start justify-between gap-4 border-b pb-2';
            li.style.borderColor = 'var(--border-color)';
            li.innerHTML = `
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">${label}</span>
                <span class="text-sm font-medium text-right" style="color: var(--text-main);">${valor || '—'}</span>
            `;
            list.appendChild(li);
        });
    }

    function leerValorBloque(block) {
        const checks = block.querySelectorAll('input[type="checkbox"]:checked');
        if (checks.length) {
            return Array.from(checks).map(c => c.parentElement.querySelector('span')?.textContent.trim() ?? c.value).join(', ');
        }
        const radio = block.querySelector('input[type="radio"]:checked');
        if (radio) return radio.parentElement.querySelector('span')?.textContent.trim() ?? radio.value;

        const sel = block.querySelector('select');
        if (sel) return sel.selectedOptions[0]?.text ?? '';

        const inp = block.querySelector('input:not([type="hidden"]), textarea');
        return inp ? inp.value : '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('jornada_id').value) {
            actualizarJornada();
        }

        const form = document.getElementById('solicitudForm');
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                const invalid = form.querySelector(':invalid');
                if (invalid) {
                    const pane = invalid.closest('.step-pane');
                    if (pane) {
                        const stepNum = parseInt(pane.id.replace('step-content-', ''));
                        goToStep(stepNum);
                        setTimeout(() => invalid.reportValidity(), 100);
                    }
                }
            }
        });
    });
</script>