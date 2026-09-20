<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-9rem)] max-w-7xl items-center justify-center">
            <div class="w-full max-w-lg">
                @include('components.alert')

                <div class="overflow-hidden rounded-2xl border shadow-lg"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="border-b px-6 py-7 text-center sm:px-8"
                        style="background-color: var(--input-bg); border-color: var(--border-color);">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                            <i class="fas fa-shield-halved text-3xl" aria-hidden="true"></i>
                        </div>
                        <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Verificar llave maestra
                        </h1>
                        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">
                            Valida tu identidad para acceder a la configuración sensible del sistema.
                        </p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <form action="{{ route('admin.configuracion.master_key.verify') }}" method="POST"
                            class="rd-prevent-double-submit">
                            @csrf
                            <div>
                                <label for="master_key" class="mb-2 block text-sm font-extrabold"
                                    style="color: var(--text-main);">
                                    Llave maestra
                                </label>
                                <div class="flex items-stretch overflow-hidden rounded-xl border focus-within:border-red-600 focus-within:ring-2 focus-within:ring-red-600/20"
                                    style="background-color: var(--input-bg); border-color: var(--input-border);">
                                    <span class="flex w-12 shrink-0 items-center justify-center border-r text-gray-400"
                                        style="border-color: var(--input-border);">
                                        <i class="fas fa-key" aria-hidden="true"></i>
                                    </span>
                                    <input type="password" name="master_key" id="master_key"
                                        class="min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-0"
                                        style="color: var(--text-main);"
                                        placeholder="Introduce tu llave maestra" required autofocus>
                                    <button type="button" id="togglePassword"
                                        class="flex w-12 shrink-0 items-center justify-center border-l text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40"
                                        style="border-color: var(--input-border);" title="Mostrar u ocultar llave">
                                        <i class="fas fa-eye" id="eyeIcon" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('master_key')
                                    <p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-md transition hover:bg-red-800 active:scale-[.99]">
                                <i class="fas fa-unlock-keyhole"></i>
                                Verificar acceso
                            </button>
                        </form>
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-gray-400">
                    <i class="fas fa-lock mr-1"></i> Esta validación protege las funciones administrativas.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>

@push('js')
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const password = document.getElementById('master_key');
            const icon = document.getElementById('eyeIcon');

            if (!password || !icon) return;

            const visible = password.type === 'text';
            password.type = visible ? 'password' : 'text';
            icon.classList.toggle('fa-eye', visible);
            icon.classList.toggle('fa-eye-slash', !visible);
        });
    </script>
@endpush
