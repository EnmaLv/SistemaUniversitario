@php
    $beneficiosSeleccionados = isset($beca)
        ? $beca->beneficios->keyBy('id')
        : collect();
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="form-group mb-3">
            <label class="font-weight-bold">Nombre de la beca</label>
            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50">
                <span class="px-3 text-slate-500"><i class="fas fa-graduation-cap"></i></span>
                <input type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                    value="{{ old('nombre', $beca->nombre ?? '') }}" placeholder="Ej: Beca comedor integral">
            </div>
            @error('nombre')
                <div class="text-danger mt-1"><b>{{ $message }}</b></div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label class="font-weight-bold">Descripcion</label>
    <textarea name="descripcion" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" placeholder="Descripcion general"
        style="resize:none;">{{ old('descripcion', $beca->descripcion ?? '') }}</textarea>
</div>

<hr>

<div class="rd-card-header mb-3">
    <h3 class="rd-title-sm">Beneficios asignados</h3>
</div>
<div class="table-responsive mb-4">
    <table class="rd-table">
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
                        <span class="rd-badge rd-badge-success">
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
                    <td colspan="4" class="text-center py-4">No hay beneficios activos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

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

<div class="rd-card-header mb-3 d-flex justify-content-between align-items-center">
    <h3 class="rd-title-sm">Tutores de la beca</h3>
    <button type="button" id="addTutorBtn" class="rd-btn rd-btn-secondary">
        <i class="fas fa-plus"></i> Agregar tutor
    </button>
</div>
<div class="table-responsive mb-4">
    <table class="rd-table" id="tutorsTable">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Tutor</th>
                <th>Descripción</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
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
                        <button type="button" class="rd-btn rd-btn-danger btn-sm remove-tutor" title="Eliminar tutor">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">No hay tutores asignados aún.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="hidden" id="addTutorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar tutor a la beca</h5>
                <button type="button" class="text-slate-500"  aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rol del tutor</label>
                            <select id="tutorRoleSelect" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input">
                                <option value="">Seleccione rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id_rol }}">{{ $role->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Persona</label>
                            <select id="tutorPersonSelect" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" disabled>
                                <option value="">Seleccione primero un rol</option>
                            </select>
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
        return ['id' => $role->id_rol, 'name' => $role->nombre];
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
        document.addEventListener('DOMContentLoaded', function() {
            const tutorTableBody = document.querySelector('#tutorsTable tbody');
            const addTutorBtn = document.getElementById('addTutorBtn');
            const roleSelect = document.getElementById('tutorRoleSelect');
            const personSelect = document.getElementById('tutorPersonSelect');
            const descriptionInput = document.getElementById('tutorDescription');
            const saveTutorModalBtn = document.getElementById('saveTutorModalBtn');
            const addTutorModalEl = document.getElementById('addTutorModal');

            const ROLES = @json($tutorJsRoles);
            const PERSONAS = @json($tutorJsPersonas);

            function buildPersonaOptions(roleId) {
                const personas = PERSONAS.filter(function(persona) {
                    return persona.role_ids.includes(parseInt(roleId, 10));
                });

                if (!personas.length) {
                    return '<option value="">No hay personas disponibles para este rol</option>';
                }

                return '<option value="">Seleccione persona</option>' + personas.map(function(persona) {
                    return `<option value="${persona.id}">${persona.name}</option>`;
                }).join('');
            }

            function getTutorRowCount() {
                return Array.from(tutorTableBody.querySelectorAll('tr')).filter(function(row) {
                    return row.querySelector('input[name^="tutores"]');
                }).length;
            }

            function updateTutorButtonLabel() {
                const rowCount = getTutorRowCount();
                addTutorBtn.innerHTML = `<i class="fas fa-plus"></i> ${rowCount ? 'Agregar otro tutor' : 'Agregar tutor'}`;
            }

            function attachRemoveHandler(button) {
                button.addEventListener('click', function() {
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
                        <button type="button" class="rd-btn rd-btn-danger btn-sm remove-tutor" title="Eliminar tutor">
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

            roleSelect.addEventListener('change', function() {
                if (!this.value) {
                    personSelect.innerHTML = '<option value="">Seleccione primero un rol</option>';
                    personSelect.disabled = true;
                    return;
                }

                personSelect.innerHTML = buildPersonaOptions(this.value);
                personSelect.disabled = false;
            });

            addTutorBtn.addEventListener('click', function() {
                addTutorModalEl.classList.remove('hidden');
            });

            addTutorModalEl.querySelectorAll('[]').forEach(function(button) {
                button.addEventListener('click', function() {
                    addTutorModalEl.classList.add('hidden');
                    resetTutorModal();
                });
            });

            saveTutorModalBtn.addEventListener('click', function() {
                const rolId = roleSelect.value;
                const tutorId = personSelect.value;
                const descripcion = descriptionInput.value.trim();

                if (!rolId || !tutorId) {
                    alert('Seleccione rol y persona antes de continuar.');
                    return;
                }

                const rolName = ROLES.find(function(role) {
                    return role.id == rolId;
                })?.name || '';

                const tutorName = PERSONAS.find(function(persona) {
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
                $('#addTutorModal').modal('hide');
            });

            document.querySelectorAll('.remove-tutor').forEach(attachRemoveHandler);
            document.querySelectorAll('.beneficio-check').forEach(function(input) {
                input.addEventListener('change', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Beneficios actualizados',
                        text: 'Recuerde guardar la beca para confirmar este cambio.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                });
            });
            updateTutorButtonLabel();
        });
    </script>
@endpush
