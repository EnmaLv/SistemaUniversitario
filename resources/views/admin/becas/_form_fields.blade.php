@php
    $beneficiosSeleccionados = isset($beca)
        ? $beca->beneficios->keyBy('id')
        : collect();
@endphp

@push('styles')
    <style>
        .beca-form-fields table th {
            padding: .85rem 1rem;
            background: rgba(0, 0, 0, .02);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .beca-form-fields table td {
            padding: .75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .beca-form-fields input,
        .beca-form-fields textarea,
        .beca-form-fields select {
            border-color: var(--border-color) !important;
            background-color: var(--input-bg) !important;
            color: var(--text-main) !important;
        }

        .beca-form-fields > .space-y-3 {
            gap: .75rem;
        }

        .beca-form-fields .table-responsive {
            overflow-x: auto;
        }

        .beca-form-fields table {
            min-width: 700px;
        }

        .beca-form-fields #benefitsTable {
            min-width: 760px;
        }

        .beca-form-fields .section-empty {
            height: 86px;
            color: var(--text-muted);
            text-align: center;
            vertical-align: middle;
            font-size: .75rem;
        }

        .beca-form-fields .section-empty i {
            display: block;
            margin-bottom: .35rem;
            font-size: 1.15rem;
            opacity: .8;
        }

        .beca-form-fields .section-header-content {
            width: 100%;
        }

        .beca-form-fields .availability-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .55rem;
            white-space: nowrap;
            border: 1px solid #047857 !important;
            border-radius: .55rem;
            background: #041c17 !important;
            color: #34d399 !important;
            font-size: .65rem;
            font-weight: 800;
            line-height: 1;
        }

        .beca-form-fields .availability-badge i {
            font-size: .6rem;
        }

        html.dark main .beca-form-fields .availability-badge,
        html:not(.dark) main .beca-form-fields .availability-badge {
            background: #041c17 !important;
            border-color: #047857 !important;
            color: #34d399 !important;
        }
    </style>
@endpush

<div class="beca-form-fields">
<div class="space-y-3">
<div class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div>
            <label class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Nombre de la beca</label>
            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50">
                <span class="px-3 text-slate-500"><i class="fas fa-graduation-cap"></i></span>
                <input type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                    value="{{ old('nombre', $beca->nombre ?? '') }}" placeholder="Ej: Beca comedor integral">
            </div>
            @error('nombre')
                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="lg:col-span-3">
    <label class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Descripción <span class="font-normal text-gray-400">(opcional)</span></label>
    <textarea name="descripcion" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" placeholder="Descripción general"
        style="resize:none;">{{ old('descripcion', $beca->descripcion ?? '') }}        </textarea>
        </div>
        </div>
        </div>

        <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
<div class="border-b px-5 py-4 flex items-center justify-between gap-3" style="border-color:var(--border-color);">

<div id="preguntasError" class="alert alert-danger mt-2" style="display:none;">Hay preguntas sin rellenar. Completa el nombre de cada pregunta antes de guardar.</div>

<div class="section-header-content mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h3 class="text-base font-extrabold flex items-center gap-3" style="color: var(--text-main);"><i class="far fa-comment-alt text-lg"></i>Preguntas de la beca</h3>
    <button type="button" id="addQuestionBtn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
        <i class="fas fa-plus"></i> Agregar pregunta
    </button>
</div>
</div>
<div class="table-responsive mb-4" id="questionsContainer">
    <style>
        /* Ocultar scrollbar sólo para el contenedor de preguntas */
        #questionsContainer { -ms-overflow-style: none; scrollbar-width: none; }
        #questionsContainer::-webkit-scrollbar { display: none; }
    </style>
    <table class="w-full border-collapse text-left" id="questionsTable">
        <thead>
            <tr>
                <th style="width:38%; padding:0 10px 12px 0;">Pregunta</th>
                <th style="width:22%; padding:0 10px 12px 0;">Tipo</th>
                <th style="width:25%; padding:0 10px 12px 0;">Limitador (para número)</th>
                <th style="width:15%; text-align:right; padding:0 0 12px 0;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php
                $preguntas = old('preguntas', isset($beca) && isset($beca->preguntas) ? $beca->preguntas->map(function($p){ return ['texto'=>$p->texto ?? '','tipo'=>$p->tipo ?? 'text','min'=>$p->min ?? '','max'=>$p->max ?? '']; })->toArray() : []);
            @endphp

            @forelse($preguntas as $index => $pregunta)
                <tr data-index="{{ $index }}" style="vertical-align:middle;">
                    <td style="padding:0 10px 0 0;">
                        <input type="text" name="preguntas[{{ $index }}][texto]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-field" value="{{ $pregunta['texto'] ?? '' }}" placeholder="Nombre de la pregunta" style="height:42px; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                    </td>
                    <td style="padding:0 10px 0 0;">
                        <select name="preguntas[{{ $index }}][tipo]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-type" style="height:42px; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                            <option value="text" {{ ($pregunta['tipo'] ?? '') == 'text' ? 'selected' : '' }}>Texto</option>
                            <option value="number" {{ ($pregunta['tipo'] ?? '') == 'number' ? 'selected' : '' }}>Número</option>
                        </select>
                    </td>
                    <td style="padding:0 10px 0 0;">
                        <div class="limit-container" style="display:{{ ($pregunta['tipo'] ?? '') == 'number' ? 'flex' : 'none' }}; gap:10px; align-items:center; width:100%;">
                            <input type="number" name="preguntas[{{ $index }}][min]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-limit-input" value="{{ $pregunta['min'] ?? '' }}" placeholder="Min" style="height:42px; width:50%; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                            <input type="number" name="preguntas[{{ $index }}][max]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-limit-input" value="{{ $pregunta['max'] ?? '' }}" placeholder="Max" style="height:42px; width:50%; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                        </div>
                    </td>
                    <td class="text-right" style="padding:0;">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400" title="Eliminar pregunta">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="section-empty"><i class="far fa-file-alt"></i>No hay preguntas agregadas aún.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
<div class="border-b px-5 py-4" style="border-color:var(--border-color);">
    <h3 class="text-base font-extrabold flex items-center gap-3" style="color: var(--text-main);"><i class="fas fa-users text-lg"></i>Beneficios asignados</h3>
</div>
<div class="table-responsive">
    <table id="benefitsTable" class="w-full border-collapse text-left">
        <thead>
            <tr>
                <th style="width:80px" class="text-center">Usar</th>
                <th>Beneficio</th>
                <th>Disponibilidad</th>
                <th>Observacion</th>
            </tr>
        </thead>
        <tbody>
            @forelse($beneficios as $index => $beneficio)
                @php
                    $pivot = $beneficiosSeleccionados->get($beneficio->id)?->pivot;
                    $checked = old("beneficios.$index.id", $pivot ? $beneficio->id : null);
                @endphp
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="beneficios[{{ $index }}][id]" value="{{ $beneficio->id }}"
                            class="beneficio-check" {{ $checked ? 'checked' : '' }}>
                        <input type="hidden" name="beneficios[{{ $index }}][activo]" value="1">
                    </td>
                    <td>
                        <strong>{{ $beneficio->nombre_beneficio }}</strong>
                        <div class="text-muted small">{{ $beneficio->descripcion }}</div>
                    </td>
                    <td>
                        <span class="rd-badge rd-badge-success availability-badge">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            {{ max(($beneficio->cupones_disponibles ?? 0) - ($beneficio->cupones_ocupados ?? 0), 0) }}
                            disponibles
                        </span>
                    </td>
                    <td>
                        <input type="text" name="beneficios[{{ $index }}][observacion]"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                            value="{{ old("beneficios.$index.observacion", $pivot->observacion ?? '') }}"
                            placeholder="Detalle opcional">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="section-empty"><i class="fas fa-gift"></i>No hay beneficios activos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
<div class="border-b px-5 py-4 flex items-center justify-between gap-3" style="border-color:var(--border-color);">
    <h3 class="text-base font-extrabold flex items-center gap-3" style="color: var(--text-main);"><i class="fas fa-user-friends text-lg"></i>Tutores de la beca</h3>
    <button type="button" id="addTutorBtn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
        <i class="fas fa-plus"></i> Agregar tutor
    </button>
</div>
<div class="table-responsive">
    <table class="w-full border-collapse text-left" id="tutorsTable">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Tutor</th>
                <th>Descripción</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php
                $becaTutores = old('tutores', isset($beca) ? $beca->tutores->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'rol_id' => $item->rol_id ?? optional($item->tutor?->usuarios->first()?->roles->first())->id_rol,
                        'tutor_id' => $item->tutor_id,
                        'descripcion' => $item->descripcion ?? '',
                    ];
                })->toArray() : []);
            @endphp

            @forelse($becaTutores as $index => $tutorRow)
                <tr data-index="{{ $index }}">
                    <input type="hidden" name="tutores[{{ $index }}][id]" value="{{ $tutorRow['id'] ?? '' }}">
                    <input type="hidden" name="tutores[{{ $index }}][rol_id]" value="{{ $tutorRow['rol_id'] ?? '' }}">
                    <input type="hidden" name="tutores[{{ $index }}][tutor_id]" value="{{ $tutorRow['tutor_id'] ?? '' }}">
                    <input type="hidden" name="tutores[{{ $index }}][descripcion]" value="{{ $tutorRow['descripcion'] ?? '' }}">
                    <td>{{ optional($roles->firstWhere('id_rol', $tutorRow['rol_id']))->nombre ?? 'Sin rol' }}</td>
                    <td>{{ optional($tutores->firstWhere('id_persona', $tutorRow['tutor_id'])) ? trim(optional($tutores->firstWhere('id_persona', $tutorRow['tutor_id']))->nombre_persona . ' ' . optional($tutores->firstWhere('id_persona', $tutorRow['tutor_id']))->apellido_persona) : 'Sin tutor' }}</td>
                    <td>{{ $tutorRow['descripcion'] ?? '' }}</td>
                    <td class="text-center">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400 remove-tutor" title="Eliminar tutor">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="section-empty"><i class="fas fa-user-friends"></i>No hay tutores asignados aún.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
</div>

<div class="hidden" id="addTutorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar tutor a la beca</h5>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"  aria-label="Cerrar" style="border:0;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold mb-2 d-block">Rol del tutor</label>
                            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50" style="border:1px solid #d1d5db; border-radius: 12px; overflow:hidden; background:#fff;">
                                <span class="px-3 text-slate-500 border-0 bg-transparent" style="padding-left:12px; color:#6b7280;">
                                    <i class="fas fa-briefcase"></i>
                                </span>
                                <select id="tutorRoleSelect" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 border-0 shadow-none" style="background:transparent; height:46px; padding-left:10px; font-size:1rem; color:#374151;">
                                    <option value="">Seleccione rol</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id_rol }}">{{ $role->nombre ?? 'Sin nombre' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold mb-2 d-block">Persona</label>
                            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50" style="border:1px solid #d1d5db; border-radius: 12px; overflow:hidden; background:#fff;">
                                <span class="px-3 text-slate-500 border-0 bg-transparent" style="padding-left:12px; color:#6b7280;">
                                    <i class="fas fa-user"></i>
                                </span>
                                <select id="tutorPersonSelect" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 border-0 shadow-none" style="background:transparent; height:46px; padding-left:10px; font-size:1rem; color:#374151;" disabled>
                                    <option value="">Seleccione primero un rol</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea id="tutorDescription" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" rows="3" placeholder="Describe el rol completo de esta persona"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="rd-btn rd-btn-default" >Cancelar</button>
                <button type="button" id="saveTutorModalBtn" class="rd-btn rd-btn-primary">Agregar</button>
            </div>
        </div>
    </div>
</div>

@php
    $tutorJsRoles = $roles->map(function ($role) {
        return ['id' => $role->id_rol, 'name' => $role->nombre ?? 'Sin nombre'];
    })->toArray();

    $tutorJsPersonas = $tutores->map(function ($tutor) {
        return [
            'id' => $tutor->id_persona,
            'name' => trim($tutor->nombre_persona . ' ' . $tutor->apellido_persona),
            'role_ids' => $tutor->usuarios->flatMap(function ($usuario) {
                return $usuario->roles->pluck('id_rol');
            })->unique()->values()->all(),
        ];
    })->toArray();
@endphp

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tutorTableBody = document.querySelector('#tutorsTable tbody');
            const addTutorBtn = document.getElementById('addTutorBtn');
            const roleSelect = document.getElementById('tutorRoleSelect');
            const personSelect = document.getElementById('tutorPersonSelect');
            const descriptionInput = document.getElementById('tutorDescription');
            const saveTutorModalBtn = document.getElementById('saveTutorModalBtn');

            const ROLES = @json($tutorJsRoles);

            const PERSONAS = @json($tutorJsPersonas);

            function buildPersonaOptions(roleId) {
                const personas = PERSONAS.filter(function (persona) {
                    return persona.role_ids.includes(parseInt(roleId, 10));
                });

                if (!personas.length) {
                    return '<option value="">No hay personas disponibles para este rol</option>';
                }

                return '<option value="">Seleccione persona</option>' + personas.map(function (persona) {
                    return `<option value="${persona.id}">${persona.name}</option>`;
                }).join('');
            }

            function getTutorRowCount() {
                return Array.from(tutorTableBody.querySelectorAll('tr')).filter(function (row) {
                    return row.querySelector('input[name^="tutores"]');
                }).length;
            }

            function updateTutorButtonLabel() {
                const rowCount = getTutorRowCount();
                addTutorBtn.innerHTML = `<i class="fas fa-plus"></i> ${rowCount ? 'Agregar otro tutor' : 'Agregar tutor'}`;
            }

            function attachRemoveHandler(button) {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    row.remove();
                    if (!getTutorRowCount()) {
                        tutorTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No hay tutores asignados aún.</td></tr>';
                    }
                    updateTutorButtonLabel();
                });
            }

            function buildTutorRow(index, value = {}) {
                const row = document.createElement('tr');
                row.dataset.index = index;
                row.innerHTML = `
                    <td>
                        <input type="hidden" name="tutores[${index}][id]" value="${value.id ?? ''}">
                        <input type="hidden" name="tutores[${index}][rol_id]" value="${value.rol_id ?? ''}">
                        <input type="hidden" name="tutores[${index}][tutor_id]" value="${value.tutor_id ?? ''}">
                        <input type="hidden" name="tutores[${index}][descripcion]" value="${value.descripcion ?? ''}">
                        ${value.rol_name || ''}
                    </td>
                    <td>${value.tutor_name || ''}</td>
                    <td>${value.descripcion || ''}</td>
                    <td class="text-center">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400 remove-tutor" title="Eliminar tutor">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                attachRemoveHandler(row.querySelector('.remove-tutor'));
                return row;
            }

            function resetTutorModal() {
                roleSelect.value = '';
                personSelect.innerHTML = '<option value="">Seleccione primero un rol</option>';
                personSelect.disabled = true;
                descriptionInput.value = '';
            }

            roleSelect.addEventListener('change', function () {
                if (!this.value) {
                    personSelect.innerHTML = '<option value="">Seleccione primero un rol</option>';
                    personSelect.disabled = true;
                    return;
                }

                personSelect.innerHTML = buildPersonaOptions(this.value);
                personSelect.disabled = false;
            });

            const addTutorModalEl = document.getElementById('addTutorModal');
            const modalCloseButtons = addTutorModalEl.querySelectorAll(
                'button[aria-label="Cerrar"], .modal-footer .rd-btn-default'
            );

            addTutorBtn.addEventListener('click', function () {
                addTutorModalEl.classList.remove('hidden');
            });

            modalCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    addTutorModalEl.classList.add('hidden');
                    resetTutorModal();
                });
            });

            saveTutorModalBtn.addEventListener('click', function () {
                const rolId = roleSelect.value;
                const tutorId = personSelect.value;
                const descripcion = descriptionInput.value.trim();

                if (!rolId || !tutorId) {
                    alert('Seleccione rol y persona antes de continuar.');
                    return;
                }

                const rolName = ROLES.find(function (role) {
                    return role.id == rolId;
                })?.name || '';

                const tutorName = PERSONAS.find(function (persona) {
                    return persona.id == tutorId;
                })?.name || '';

                const placeholder = tutorTableBody.querySelector('tr td[colspan="4"]');
                if (placeholder) {
                    placeholder.closest('tr').remove();
                }

                const index = getTutorRowCount();
                const newRow = buildTutorRow(index, {
                    id: '',
                    rol_id: rolId,
                    tutor_id: tutorId,
                    descripcion: descripcion,
                    rol_name: rolName,
                    tutor_name: tutorName,
                });
                tutorTableBody.appendChild(newRow);
                updateTutorButtonLabel();
                resetTutorModal();
                addTutorModalInstance.hide();
            });

            tutorTableBody.querySelectorAll('.remove-tutor').forEach(attachRemoveHandler);
            updateTutorButtonLabel();

            // Preguntas dinámicas
            const questionsTableBody = document.querySelector('#questionsTable tbody');
            const addQuestionBtn = document.getElementById('addQuestionBtn');

            function getQuestionRowCount() {
                return Array.from(questionsTableBody.querySelectorAll('tr')).filter(function (row) {
                    return row.querySelector('input[name^="preguntas"]');
                }).length;
            }

            function attachQuestionRemove(button) {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    row.remove();
                    if (!getQuestionRowCount()) {
                        questionsTableBody.innerHTML = '<tr><td colspan="4" class="section-empty"><i class="far fa-file-alt"></i>No hay preguntas agregadas aún.</td></tr>';
                    }
                });
            }

            function onTypeChange(select) {
                select.addEventListener('change', function () {
                    const tr = this.closest('tr');
                    const container = tr.querySelector('.limit-container');
                    if (this.value === 'number') {
                        container.style.display = 'flex';
                    } else {
                        container.style.display = 'none';
                    }
                });
            }

            function buildQuestionRow(index) {
                const row = document.createElement('tr');
                row.dataset.index = index;
                row.innerHTML = `
                    <td style="padding:0 10px 0 0;">
                        <input type="text" name="preguntas[${index}][texto]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-field" placeholder="Nombre de la pregunta" style="height:42px; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                    </td>
                    <td style="padding:0 10px 0 0;">
                        <select name="preguntas[${index}][tipo]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-type" style="height:42px; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                        </select>
                    </td>
                    <td style="padding:0 10px 0 0;">
                        <div class="limit-container" style="display:none; gap:10px; align-items:center; width:100%;">
                            <input type="number" name="preguntas[${index}][min]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-limit-input" placeholder="Min" style="height:42px; width:50%; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                            <input type="number" name="preguntas[${index}][max]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 question-limit-input" placeholder="Max" style="height:42px; width:50%; border:1px solid #d1d5db; border-radius:10px; background:#fff; box-shadow:none;">
                        </div>
                    </td>
                    <td class="text-right" style="padding:0;">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400 remove-question" title="Eliminar pregunta">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                attachQuestionRemove(row.querySelector('.remove-question'));
                onTypeChange(row.querySelector('.question-type'));
                return row;
            }

            addQuestionBtn.addEventListener('click', function () {
                const placeholder = questionsTableBody.querySelector('tr td[colspan="4"]');
                if (placeholder) {
                    placeholder.closest('tr').remove();
                }

                const index = getQuestionRowCount();
                const newRow = buildQuestionRow(index);
                questionsTableBody.appendChild(newRow);
            });

            function validateQuestionFields() {
                const errorBox = document.getElementById('preguntasError');
                const rows = questionsTableBody.querySelectorAll('tr');
                let hasEmpty = false;
                let hasMinMaxError = false;

                rows.forEach(function (row) {
                    if (row.querySelector('td[colspan="4"]')) {
                        return;
                    }

                    const input = row.querySelector('input[name^="preguntas"][name$="[texto]"]');
                    if (input && !input.value.trim()) {
                        hasEmpty = true;
                        input.style.borderColor = '#dc3545';
                        input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.15)';
                    } else if (input) {
                        input.style.borderColor = '#d1d5db';
                        input.style.boxShadow = 'none';
                    }

                    // validar min/max si aplica
                    const tipo = row.querySelector('.question-type');
                    if (tipo && tipo.value === 'number') {
                        const minInput = row.querySelector('input[name^="preguntas"][name$="[min]"]');
                        const maxInput = row.querySelector('input[name^="preguntas"][name$="[max]"]');
                        const minVal = minInput && minInput.value !== '' ? parseFloat(minInput.value) : null;
                        const maxVal = maxInput && maxInput.value !== '' ? parseFloat(maxInput.value) : null;
                        if (minVal !== null && maxVal !== null && !isNaN(minVal) && !isNaN(maxVal) && minVal > maxVal) {
                            hasMinMaxError = true;
                            if (minInput) {
                                minInput.style.borderColor = '#dc3545';
                                minInput.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.15)';
                            }
                            if (maxInput) {
                                maxInput.style.borderColor = '#dc3545';
                                maxInput.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.15)';
                            }
                        } else if (minInput && maxInput) {
                            minInput.style.borderColor = '#d1d5db';
                            minInput.style.boxShadow = 'none';
                            maxInput.style.borderColor = '#d1d5db';
                            maxInput.style.boxShadow = 'none';
                        }
                    }
                });

                if (hasEmpty) {
                    errorBox.style.display = 'block';
                    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return false;
                }

                if (hasMinMaxError) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Rango inválido',
                            text: 'El valor mínimo no puede ser mayor que el máximo en una pregunta numérica.',
                        });
                    } else {
                        alert('El valor mínimo no puede ser mayor que el máximo en una pregunta numérica.');
                    }
                    return false;
                }

                errorBox.style.display = 'none';
                return true;
            }

            const form = document.querySelector('form.rd-prevent-double-submit');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!validateQuestionFields()) {
                        event.preventDefault();
                    }
                });
            }

            // validación en tiempo real para inputs min/max
            function attachMinMaxListener(input) {
                input.addEventListener('change', function () {
                    const row = input.closest('tr');
                    const tipo = row.querySelector('.question-type');
                    if (!tipo || tipo.value !== 'number') return;
                    const minInput = row.querySelector('input[name^="preguntas"][name$="[min]"]');
                    const maxInput = row.querySelector('input[name^="preguntas"][name$="[max]"]');
                    const minVal = minInput && minInput.value !== '' ? parseFloat(minInput.value) : null;
                    const maxVal = maxInput && maxInput.value !== '' ? parseFloat(maxInput.value) : null;
                    if (minVal !== null && maxVal !== null && !isNaN(minVal) && !isNaN(maxVal) && minVal > maxVal) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'error', title: 'Rango inválido', text: 'El valor mínimo no puede ser mayor que el máximo.' });
                        } else {
                            alert('El valor mínimo no puede ser mayor que el máximo.');
                        }
                        if (minInput) minInput.focus();
                    }
                });
            }

            questionsTableBody.querySelectorAll('.question-limit-input').forEach(attachMinMaxListener);
            questionsTableBody.querySelectorAll('.question-type').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    const tr = this.closest('tr');
                    // cuando cambia a number, attach listeners a sus inputs
                    const min = tr.querySelector('input[name^="preguntas"][name$="[min]"]');
                    const max = tr.querySelector('input[name^="preguntas"][name$="[max]"]');
                    if (min) attachMinMaxListener(min);
                    if (max) attachMinMaxListener(max);
                });
            });

            questionsTableBody.querySelectorAll('.remove-question').forEach(attachQuestionRemove);
            questionsTableBody.querySelectorAll('.question-type').forEach(onTypeChange);
        });
    </script>
@endpush

</div>
