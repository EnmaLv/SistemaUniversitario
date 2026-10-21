<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aviso de Atención') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border-l-8 border-yellow-500">
                <div class="p-8 bg-white border-b border-gray-200">
                    <div class="text-center mb-6">
                        <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="mt-4 text-xl leading-6 font-bold text-gray-900">Aviso de Atención al Paciente</h3>
                    </div>

                    <div class="text-gray-700 space-y-4 mb-8 text-center text-lg">
                        <p>
                            El sistema ha detectado que has <strong>rechazado o cancelado</strong> al menos 3 citas relacionadas al paciente <strong class="text-gray-900">{{ $cita->paciente->nombre_completo ?? 'Desconocido' }}</strong>.
                        </p>
                        <p>
                            Este aviso es un recordatorio para asegurar una adecuada atención. Debido a que las cancelaciones o rechazos han sido por parte del profesional, puede que desees otorgarle preferencia en sus próximas solicitudes para no afectar su proceso.
                        </p>
                        <p class="font-semibold mt-4">
                            ¿Deseas cambiar manualmente la prioridad de todas sus solicitudes pendientes a "Alta"?
                        </p>
                    </div>

                    <div class="flex justify-center gap-4 mt-8">
                        <form method="POST" action="{{ route('admin.psicologia.maestros.citas.update_alerta_prioridad', $cita->id_cps) }}">
                            @csrf
                            <input type="hidden" name="prioridad" value="alta">
                            <button type="submit" class="inline-flex justify-center w-full px-6 py-3 bg-yellow-600 text-white font-medium text-sm leading-snug rounded-[25px] shadow-md hover:bg-yellow-700 hover:shadow-lg focus:bg-yellow-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-yellow-800 active:shadow-lg transition duration-150 ease-in-out">
                                Sí, cambiar a Alta
                            </button>
                        </form>

                        <a href="{{ route('admin.psicologia.maestros.agenda.index') }}" class="inline-flex justify-center w-full px-6 py-3 bg-gray-500 text-white font-medium text-sm leading-snug rounded-[25px] shadow-md hover:bg-gray-600 hover:shadow-lg focus:bg-gray-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-gray-700 active:shadow-lg transition duration-150 ease-in-out items-center text-center">
                            No, mantener igual e ignorar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
