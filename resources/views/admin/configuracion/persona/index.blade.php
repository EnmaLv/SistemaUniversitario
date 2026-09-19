<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Estudiantes
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.configuracion.persona.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Registrar estudiante</span>
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">
                <form action="{{ route('admin.configuracion.persona.index') }}" method="GET" class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre o cédula..."
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </form>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full min-w-[800px] text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width:80px;">#</th>
                                <th class="px-6 py-4 text-center">Cédula</th>
                                <th class="px-6 py-4 text-center">Nombre</th>
                                <th class="px-6 py-4 text-center">Apellido</th>
                                <th class="px-6 py-4 text-center">Correo electrónico</th>
                                <th class="px-6 py-4 text-center" style="width:120px;">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="text-xs font-medium">
                            @forelse($personas as $persona)
                                <x-table-row :id="$persona->id_persona" class="border-b"
                                    style="border-color: var(--border-color);">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($personas->currentPage() - 1) * $personas->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $persona->cedula_persona }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap"
                                        style="color: var(--text-main);">
                                        {{ $persona->nombre_persona }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap"
                                        style="color: var(--text-main);">
                                        {{ $persona->apellido_persona }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $persona->email_persona ?: 'Sin correo' }}
                                    </td>

                                    <x-table-actions :id="$persona->id_persona" baseUrl="admin/configuracion/persona"
                                        :show="false" :edit="false" :toggle="false">
                                        <a href="{{ route('admin.configuracion.persona.show', ['id' => $persona->id_persona]) }}"
                                            onclick="event.stopPropagation()"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                                            title="Ver estudiante">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.configuracion.persona.edit', ['id' => $persona->id_persona]) }}"
                                            onclick="event.stopPropagation()"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-950/50 transition-colors"
                                            title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    </x-table-actions>
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        No hay estudiantes registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($personas->hasPages())
                    <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                        {{ $personas->onEachSide(1)->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
