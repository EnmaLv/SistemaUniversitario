<div class="w-full">
    <form wire:submit="{{ $isEdit ? 'update' : 'create' }}" method="POST" class="rd-prevent-double-submit">
        @csrf
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm p-6 sm:p-8">
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b" style="border-color: var(--border-color);">
                    <span
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-id-card-alt text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-widest" style="color: var(--text-main);">
                        Identidad y sistema
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="cedula"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Cédula / ID
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all {{ $errors->has('cedula') ? 'border-rose-400' : '' }}"
                            style="border-color: {{ $errors->has('cedula') ? '' : 'var(--border-color)' }};">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-fingerprint text-sm"></i>
                            </span>
                            <input type="text" inputmode="numeric" wire:model.lazy="cedula" minlength="7" maxlength="9"
                                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 9)" name="cedula"
                                id="cedula" placeholder="25123456" value="{{ old('cedula') }}"
                                {{ $onlyShow ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('cedula')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_nacimiento"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Fecha de nacimiento
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-calendar-day text-sm"></i>
                            </span>
                            <input wire:model="fecha_nacimiento" type="date" name="fecha_nacimiento"
                                id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                                max="{{ \Carbon\Carbon::now()->subYears(15)->format('Y-m-d') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('fecha_nacimiento')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b" style="border-color: var(--border-color);">
                    <span
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-user text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-widest" style="color: var(--text-main);">
                        Nombres y apellidos
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="nombre"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Primer nombre
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <input wire:model="nombre" type="text" name="nombre" id="nombre" placeholder="Juan"
                                value="{{ old('nombre') }}" {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('nombre')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="segundo_nombre"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Segundo nombre
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <input wire:model="segundo_nombre" type="text" name="segundo_nombre" id="segundo_nombre"
                                placeholder="Opcional" value="{{ old('segundo_nombre') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('segundo_nombre')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Primer apellido
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <input wire:model="apellido" type="text" name="apellido" id="apellido"
                                placeholder="Pérez" value="{{ old('apellido') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('apellido')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="segundo_apellido"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Segundo apellido
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <input wire:model="segundo_apellido" type="text" name="segundo_apellido"
                                id="segundo_apellido" placeholder="Opcional" value="{{ old('segundo_apellido') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('segundo_apellido')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b" style="border-color: var(--border-color);">
                    <span
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-address-book text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-widest" style="color: var(--text-main);">
                        Información de contacto
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="genero"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Género
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-venus-mars text-sm"></i>
                            </span>
                            <select wire:model="genero" name="genero" id="genero"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="" selected>Seleccione</option>
                                <option value="MASCULINO">Masculino</option>
                                <option value="FEMENINO">Femenino</option>
                            </select>
                        </div>
                        @error('genero')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefono"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Teléfono móvil
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-mobile-alt text-sm"></i>
                            </span>
                            <input wire:model="telefono" id="telefono" type="text" name="telefono"
                                placeholder="412 123-4567" value="{{ old('telefono') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('telefono')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Correo electrónico
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-at text-sm"></i>
                            </span>
                            <input wire:model="email" type="email" name="email" id="email"
                                placeholder="usuario@gmail.com" value="{{ old('email') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b" style="border-color: var(--border-color);">
                    <span
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-graduation-cap text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-widest" style="color: var(--text-main);">
                        Información académica
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="pnfId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            PNF
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all {{ $errors->has('pnfId') ? 'border-rose-400' : '' }}"
                            style="border-color: {{ $errors->has('pnfId') ? '' : 'var(--border-color)' }};">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-university text-sm"></i>
                            </span>
                            <select wire:model="pnfId" id="pnfId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione PNF</option>
                                @foreach ($pnfs as $pnf)
                                    <option value="{{ $pnf->id_pnf }}"
                                        {{ old('pnf_id', request('pnf_id')) == $pnf->id_pnf ? 'selected' : '' }}>
                                        {{ $pnf->nombre_pnf }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('pnfId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">El PNF es obligatorio</p>
                        @enderror

                        @if (!$isEdit)
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                ¿No lo encuentras?
                                <a href="{{ route('admin.maestros.pnf.index', ['from' => url()->previous()]) }}"
                                    onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};"
                                    class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                    Créalo aquí
                                </a>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="sedeId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Sede del estudiante
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all {{ $errors->has('sedeId') ? 'border-rose-400' : '' }}"
                            style="border-color: {{ $errors->has('sedeId') ? '' : 'var(--border-color)' }};">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-building text-sm"></i>
                            </span>
                            <select wire:model="sedeId" id="sedeId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione sede</option>
                                @foreach ($sede as $sed)
                                    <option value="{{ $sed->id }}"
                                        {{ old('sede_id', request('sede_id')) == $sed->id ? 'selected' : '' }}>
                                        {{ $sed->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('sedeId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">La sede es obligatoria</p>
                        @enderror

                        @if (!$isEdit)
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                ¿No la encuentras?
                                <a href="{{ route('admin.maestros.sedes.create', ['from' => url()->current()]) }}"
                                    onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};"
                                    class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                    Créala aquí
                                </a>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="semestreId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Semestre del estudiante
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all {{ $errors->has('semestreId') ? 'border-rose-400' : '' }}"
                            style="border-color: {{ $errors->has('semestreId') ? '' : 'var(--border-color)' }};">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-layer-group text-sm"></i>
                            </span>
                            <select wire:model="semestreId" id="semestreId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione semestre</option>
                                @php
                                    $ordinales = [
                                        1 => '1er',
                                        2 => '2do',
                                        3 => '3er',
                                        4 => '4to',
                                        5 => '5to',
                                        6 => '6to',
                                        7 => '7mo',
                                        8 => '8vo',
                                        9 => '9no',
                                        10 => '10mo',
                                    ];
                                @endphp
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $ordinales[$i] . ' SEMESTRE' }}">
                                        {{ $ordinales[$i] }} Semestre
                                    </option>
                                @endfor
                            </select>
                        </div>
                        @error('semestreId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">El semestre es obligatorio</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b" style="border-color: var(--border-color);">
                    <span
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-map-marked-alt text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-widest" style="color: var(--text-main);">
                        Ubicación y residencia
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {{-- Estado --}}
                    <div>
                        <label for="estadosVeId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Estado
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-map text-sm"></i>
                            </span>
                            <select wire:model.lazy="estadosVeId" name="estado_id" id="estadosVeId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione estado</option>
                                @foreach ($estadosVE as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('estadosVeId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                        @if (!$isEdit)
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                ¿No está en la lista?
                                <a href="{{ route('admin.estado.index', ['from' => url()->current()]) }}"
                                    onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};"
                                    class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                    Créalo aquí
                                </a>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="municipiosId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Municipio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-city text-sm"></i>
                            </span>
                            <select wire:model.lazy="municipiosId" name="municipio_id" id="municipiosId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                @if (!$enabledMunicipio) disabled @endif
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione municipio</option>
                                @foreach ($municipiosVE as $municipio)
                                    <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('municipiosId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                        @if (!$isEdit)
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                ¿No está en la lista?
                                <a href="{{ route('admin.municipio.index', ['from' => url()->current()]) }}"
                                    onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};"
                                    class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                    Créalo aquí
                                </a>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="parroquiaId"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Localidad
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-map-pin text-sm"></i>
                            </span>
                            <select wire:model.lazy="parroquiaId" name="parroquia_id" id="parroquiaId"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                @if (!$enabledParroquia) disabled @endif
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                                <option value="">Seleccione localidad</option>
                                @foreach ($parroquiasVE as $parroquia)
                                    <option value="{{ $parroquia->id }}">{{ $parroquia->nombre_localidad }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('parroquiaId')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                        @if (!$isEdit)
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                ¿No está en la lista?
                                <a href="{{ route('admin.localidad.index', ['from' => url()->current()]) }}"
                                    onclick="return !{{ $onlyShow || !$formHabilitado ? 'true' : 'false' }};"
                                    class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                    Créala aquí
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="calle"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Calle / Avenida
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-road text-sm"></i>
                            </span>
                            <input wire:model="calle" type="text" name="calle" id="calle"
                                placeholder="Ej: Av. Francisco de Miranda" value="{{ old('calle') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('calle')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sector"
                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                            Sector / Urbanización
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-building text-sm"></i>
                            </span>
                            <input wire:model="sector" type="text" name="sector" id="sector"
                                placeholder="Ej: Urb. Los Palos Grandes" value="{{ old('sector') }}"
                                {{ $onlyShow || !$formHabilitado ? 'disabled' : '' }}
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-50">
                        </div>
                        @error('sector')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            @if (!$onlyShow)
                <div class="mt-8 pt-6 border-t flex flex-col sm:flex-row items-center justify-end gap-3"
                    style="border-color: var(--border-color);">
                    <button type="reset" wire:click="$set('formHabilitado', false)"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-undo text-xs"></i> Restablecer
                    </button>
                    <button type="submit" {{ !$formHabilitado ? 'disabled' : '' }}
                        class="w-full sm:w-auto rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save text-xs"></i> {{ $isEdit ? 'Actualizar' : 'Registrar' }} estudiante
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>

@script
    <script>
        $wire.on('alert', (payload) => {
            const data = Array.isArray(payload) ? (payload[0] ?? {}) : payload;

            Swal.fire({
                icon: data.type || 'info',
                title: data.title || '',
                text: data.text || '',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6',
            });
        });

        $wire.on('confirm-reactivate', (payload) => {
            const data = Array.isArray(payload) ? (payload[0] ?? {}) : payload;

            Swal.fire({
                title: 'Estudiante inactivo detectado',
                text: 'Este estudiante está inactivo. ¿Desea reactivarlo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.reactivarEstudiante(data.cedula);
                }
            });
        });
    </script>
@endscript

<script>
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'telefono') {
            let value = e.target.value.replace(/\D/g, '').substring(0, 10);

            if (value.length > 3) {
                value = value.substring(0, 3) + ' ' + value.substring(3);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + '-' + value.substring(7);
            }

            e.target.value = value;
        }

        if (e.target && e.target.name === 'cedula') {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 8) value = value.slice(0, 8);
            e.target.value = value;
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.jQuery && jQuery.fn.inputmask) {
            jQuery('[data-mask]').inputmask();
        }
    });
</script>
