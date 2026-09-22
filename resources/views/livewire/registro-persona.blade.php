<div class="row fade-in">
    <div class="col-md-12 m-auto">
        <form wire:submit="{{ $isEdit ? 'update' : 'create' }}" method="POST" class="rd-prevent-double-submit">
            @csrf

            <!-- Identidad y Sistema -->
            <div class="mb-4">
                <h5 class="rd-title-sm text-muted mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-id-card-alt mr-1"></i> Identidad y Sistema
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Cédula / ID</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group {{ $errors->has('cedula') ? 'border-danger' : '' }}">
                            <span><i class="fas fa-fingerprint"></i></span>
                            <input type="number" wire:model.lazy="cedula" min="7" name="cedula" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="25123456" value="{{ old('cedula') }}"
                            {{ $onlyShow ? 'disabled' : '' }}>
                        </div>
                        @error('cedula')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Fecha de Nacimiento</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-calendar-day"></i></span>
                            <input wire:model="fecha_nacimiento" type="date" name="fecha_nacimiento" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" 
                            {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" value="{{ old('fecha_nacimiento') }}" 
                            max="{{ \Carbon\Carbon::now()->subYears(15)->format('Y-m-d') }}">
                        </div>
                        @error('fecha_nacimiento')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Nombres y Apellidos -->
            <div class="mb-4">
                <h5 class="rd-title-sm text-muted mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-user mr-1"></i> Nombres y Apellidos
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="rd-label">Primer Nombre</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <input wire:model="nombre" type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                     style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Juan" value="{{ old('nombre') }}">
                                </div>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="rd-label">Segundo Nombre</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <input wire:model="segundo_nombre" type="text" name="segundo_nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                    style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Opcional" value="{{ old('segundo_nombre') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="rd-label">Primer Apellido</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <input wire:model="apellido" type="text" name="apellido" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                     style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Pérez" value="{{ old('apellido') }}">
                                </div>
                                @error('apellido')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="rd-label">Segundo Apellido</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <input wire:model="segundo_apellido" type="text" name="segundo_apellido" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Opcional" value="{{ old('segundo_apellido') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="mb-4">
                <h5 class="rd-title-sm text-muted mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-address-book mr-1"></i> Información de Contacto
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Género</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-venus-mars"></i></span>
                            <select wire:model="genero" name="genero" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}">
                                <option value="" selected>Seleccione</option>
                                <option value="MASCULINO">MASCULINO</option>
                                <option value="FEMENINO">FEMENINO</option>
                            </select>                            
                        </div>
                        @error('genero')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Teléfono Móvil</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-mobile-alt"></i></span>
                            <input wire:model="telefono" id="telefono" type="text" name="telefono" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="412 123-4567" value="{{ old('telefono') }}">
                        </div>
                        @error('telefono')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Correo Electrónico</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-at"></i></span>
                            <input wire:model="email" type="email" name="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="usuario@gmail.com" value="{{ old('email') }}">
                        </div>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Académica -->
            <div class="mb-4 fade-in">
                <h5 class="rd-title-sm text-muted mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-graduation-cap mr-1"></i> Información Académica
                </h5>

                <div class="row">
                    <div class="col-md-4 mb-3 fade-in">
                        <label class="rd-label">PNF</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-university"></i></span>
                            <select wire:model="pnfId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                            style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}">
                                <option value="">Seleccione PNF</option>
                                @foreach($pnfs as $pnf)
                                    <option value="{{ $pnf->id_pnf }}" {{ old('pnf_id', request('pnf_id')) == $pnf->id_pnf ? 'selected' : '' }}>{{ $pnf->nombre_pnf }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('pnfId') <small class="text-danger">El PNF es obligatorio</small> @enderror
                        <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            @if(!$isEdit)
                            <small style="color: #64748b; font-size: 0.85rem;">
                                ¿No encuentras lo que buscas?
                                <a {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }} color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;" href="{{ route('admin.maestros.pnf.index', [
                                    'from' => url()->previous()
                                    ]) }}" onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};">
                                    Créalo aquí
                                </a>
                            </small>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Sede del Estudiante</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-university"></i></span>
                            <select wire:model="sedeId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                            style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}">
                                <option value="">Seleccione Sede</option>
                                @foreach($sede as $sed)
                                    <option value="{{ $sed->id }}" {{ old('sede_id', request('sede_id')) == $sed->id ? 'selected' : '' }}>
                                        {{ $sed->nombre ?? $sed->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('sedeId') <small class="text-danger">La Sede es obligatoria</small> @enderror
                        <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            @if(!$isEdit)
                            <small style="color: #64748b; font-size: 0.85rem;">
                                <a {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }} color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;" href="{{ route('admin.maestros.sedes.create', [
                                    'from' => url()->current()
                                ]) }}" onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};">
                                    Créala aquí
                                </a>
                            </small>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Semestre del Estudiante</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-university"></i></span>
                            <select wire:model="semestreId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                            style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}">
                                <option value="">Seleccione Semestre</option>
                                @php
                                    $ordinales = [
                                        1 => '1er', 2 => '2do', 3 => '3er', 4 => '4to', 5 => '5to', 
                                        6 => '6to', 7 => '7mo', 8 => '8vo', 9 => '9no', 10 => '10mo'
                                    ];
                                @endphp

                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $ordinales[$i] . ' SEMESTRE' }}">
                                        {{ $ordinales[$i] }} Semestre
                                    </option>
                                @endfor
                            </select>
                        </div>
                        @error('semestreId') <small class="text-danger">El Semestre es obligatorio</small> @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-top: 2px dashed #eef2f6;">

            <!-- Ubicación y Residencia -->
            <div class="mb-3">
                <h5 class="rd-title-sm text-muted mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ubicación y Residencia
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Estado</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-map"></i></span>
                            <select wire:model.lazy="estadosVeId" name="estado_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}">
                                <option value="">Seleccione Estado</option>
                                @foreach($estadosVE as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('estadosVeId')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            @if(!$isEdit)
                            <small style="color: #64748b; font-size: 0.85rem;">
                                <a {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }} color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;" href="{{ route('admin.estado.index', [
                                    'from' => url()->current()
                                ]) }}" onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};">
                                    Créalo aquí
                                </a>
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Municipio</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <select wire:model.lazy="municipiosId" name="municipio_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                             {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" @if(!$enabledMunicipio) disabled @endif>
                                <option value="">Seleccione Municipio</option>
                                @foreach($municipiosVE as $municipio)
                                    <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('municipiosId')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            @if(!$isEdit)
                            <small style="color: #64748b; font-size: 0.85rem;">
                                <a {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }} color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;" href="{{ route('admin.municipio.index', [
                                    'from' => url()->current()
                                ]) }}" onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};">
                                    Créalo aquí
                                </a>
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="rd-label">Localidad</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <select wire:model.lazy="parroquiaId" name="parroquia_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" 
                            {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" @if(!$enabledParroquia) disabled @endif>
                                <option value="">Seleccione Localidad</option>
                                @foreach($parroquiasVE as $parroquia)
                                    <option value="{{ $parroquia->id }}">{{ $parroquia->nombre_localidad }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('parroquiaId')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                            @if(!$isEdit)
                                <small style="color: #64748b; font-size: 0.85rem;">
                                    <a wire:model.lazy="parroquiaId" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} 
                                    style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }} color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;" href="{{ route('admin.localidad.index', [
                                        'from' => url()->current()
                                    ]) }}" onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};">
                                        Créala aquí
                                    </a>
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    {{-- Calle --}}
                    <div class="col-md-6 mb-3">
                        <label class="rd-label">Calle / Avenida</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-road"></i></span>
                            <input wire:model="calle" type="text" name="calle" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Ej: Av. Francisco de Miranda" value="{{ old('calle') }}">
                        </div>
                        @error('calle')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    {{-- Sector / Urbanización --}}
                    <div class="col-md-6 mb-3">
                        <label class="rd-label">Sector / Urbanización</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-building"></i></span>
                            <input wire:model="sector" type="text" name="sector" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }} style="{{ $onlyShow || !$formHabilitado ? 'opacity: 0.5;' : '' }}" placeholder="Ej: Urb. Los Palos Grandes" value="{{ old('sector') }}">
                        </div>
                        @error('sector')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            @if(!$onlyShow)
            <div class="mt-5 flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);">
                <button type="reset"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300"
                    wire:click="$set('formHabilitado', false)">
                    <i class="fas fa-undo text-xs"></i> Restablecer
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95"
                    {{ !$formHabilitado ? 'disabled' : '' }}
                    style="{{ !$formHabilitado ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                    <i class="fas fa-save text-xs"></i> {{ $isEdit ? 'Actualizar' : 'Registrar' }} estudiante
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

@section('js')
    <script>
       document.addEventListener('livewire:init', () => {
            Livewire.on('confirm-reactivate', (event) => {
                Swal.fire({
                    title: 'Estudiante inactivo detectado',
                    text: 'Este estudiante está inactivo. ¿Desea reactivarlo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.reactivarEstudiante(event.cedula);
                    }
                });
            });
        });

        $(document).ready(function() {
            if ($.fn.inputmask) {
                $("[data-mask]").inputmask();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const telefonoInput = document.querySelector('#telefono');
            if (telefonoInput) {
                telefonoInput.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.substring(0, 10);
                    
                    // Formatear espacio y guión: 412 123-4567
                    if (value.length > 3) {
                        value = value.substring(0, 3) + ' ' + value.substring(3);
                    }
                    if (value.length > 7) {
                        value = value.substring(0, 7) + '-' + value.substring(7);
                    }
                    
                    e.target.value = value;
                });
            }
            
            const cedulaInput = document.querySelector('input[name="cedula"]');
            if (cedulaInput) {
                cedulaInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 8) {
                        this.value = this.value.slice(0, 8);
                    }
                });
            }

            document.addEventListener('livewire:initialized', () => {
                @this.on('alert', (params) => {
                    Swal.fire({
                        icon: params.type,
                        title: params.title,
                        text: params.text,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#3085d6',
                    });
                });
            });
        });
    </script>
@endsection