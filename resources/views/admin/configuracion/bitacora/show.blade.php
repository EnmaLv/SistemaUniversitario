<x-app-layout>

    @include('components.alert')

    @php
        $mostrar = function ($valor) {
            if (is_null($valor)) {
                return '—';
            }
            if (is_bool($valor)) {
                return $valor ? 'true' : 'false';
            }
            if (is_array($valor)) {
                return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            return (string) $valor;
        };
        $datos = [
            'Fecha y hora'      => $registro->fecha_hora?->format('d/m/Y H:i:s'),
            'Nivel'             => ucfirst($registro->nivel),
            'Módulo'            => $registro->modulo,
            'Acción'            => $registro->accion,
            'Usuario'           => ($registro->nombre_usuario ?? 'Sistema') . ($registro->usuario_id ? ' (ID ' . $registro->usuario_id . ')' : ''),
            'Tabla afectada'    => $registro->tabla_afectada,
            'Registro'          => $registro->registro_id,
            'Método HTTP'       => $registro->metodo_http,
            'Ruta'              => $registro->ruta,
            'URL'               => $registro->url,
            'Código respuesta'  => $registro->codigo_respuesta,
            'Dirección IP'      => $registro->ip_address,
            'Navegador'         => $registro->navegador,
            'Sistema operativo' => $registro->sistema_operativo,
            'Sesión (hash)'     => $registro->session_id,
            'Observación'       => $registro->observacion,
        ];
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Evento #{{ $registro->id }}</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-lock mr-1" style="color:var(--color-primary)"></i>
                        Registro de solo lectura
                    </p>
                </div>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.configuracion.bitacora.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border shadow-sm lg:col-span-1" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="p-4 border-b" style="border-color:var(--border-color);">
                        <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Datos del evento</h3>
                    </div>
                    <dl class="divide-y" style="border-color:var(--border-color);">
                        @foreach ($datos as $etiqueta => $valor)
                            <div class="px-4 py-2.5">
                                <dt class="text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">{{ $etiqueta }}</dt>
                                <dd class="mt-0.5 break-all text-sm" style="color:var(--text-main);">{{ $valor !== null && $valor !== '' ? $valor : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-2xl border shadow-sm overflow-hidden lg:col-span-2" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="p-4 border-b" style="border-color:var(--border-color);">
                        <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Cambios: antes → después</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                    <th class="px-4 py-3">Campo</th>
                                    <th class="px-4 py-3">Antes</th>
                                    <th class="px-4 py-3">Después</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($diferencias as $fila)
                                    <tr class="border-b align-top" style="border-color:var(--border-color);">
                                        <td class="px-4 py-3 font-bold" style="color:var(--text-main);">
                                            {{ $fila['campo'] }}
                                            @if ($fila['cambio'])
                                                <i class="fas fa-circle text-[6px] align-middle ml-1" style="color:var(--color-primary)" title="Modificado"></i>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 {{ $fila['cambio'] ? 'bg-red-50 dark:bg-red-950/30' : '' }}">
                                            <pre class="whitespace-pre-wrap break-all font-mono text-xs" style="color:var(--text-main);">{{ $mostrar($fila['antes']) }}</pre>
                                        </td>
                                        <td class="px-4 py-3 {{ $fila['cambio'] ? 'bg-emerald-50 dark:bg-emerald-950/30' : '' }}">
                                            <pre class="whitespace-pre-wrap break-all font-mono text-xs" style="color:var(--text-main);">{{ $mostrar($fila['despues']) }}</pre>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Este evento no registra cambios de datos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
