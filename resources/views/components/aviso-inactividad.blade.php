@auth
    <style>[x-cloak] { display: none !important; }</style>
    <div x-data="avisoInactividad({
            limite: {{ (int) config('seguridad.minutos_inactividad', 15) * 60 }},
            aviso: {{ (int) config('seguridad.segundos_aviso_inactividad', 60) }},
            urlEstado: @js(route('seguridad.sesion.estado')),
            urlMantener: @js(route('seguridad.sesion.mantener'))
        })" x-init="iniciar()">
        <div x-show="visible" x-cloak x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 p-4" role="alertdialog"
            aria-modal="true" aria-labelledby="aviso-inactividad-titulo">
            <div class="w-full max-w-md rounded-2xl border p-6 text-center shadow-2xl"
                style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/50">
                    <i class="fas fa-hourglass-half text-xl text-amber-600 dark:text-amber-400"></i>
                </div>
                <h2 id="aviso-inactividad-titulo" class="text-xl font-extrabold" style="color:var(--text-main);">
                    Tu sesión está por expirar
                </h2>
                <p class="mt-2 text-sm" style="color:var(--text-main);">
                    Por seguridad, la sesión se cerrará por inactividad en
                    <strong x-text="restante"></strong> segundos.
                </p>
                <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                    <button type="button" @click="seguir()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                        <i class="fas fa-check text-xs"></i> Seguir conectado
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold"
                            style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-sign-out-alt text-xs"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function avisoInactividad(cfg) {
            const cabeceras = () => ({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            });
            const recargar = () => window.location.assign(window.location.href);

            return {
                visible: false,
                restante: cfg.aviso,
                vence: Date.now() + cfg.limite * 1000,
                consultando: false,
                temporizador: null,

                iniciar() {
                    this.temporizador = setInterval(() => this.revisar(), 1000);
                },

                quedan() {
                    return Math.round((this.vence - Date.now()) / 1000);
                },

                async revisar() {
                    let quedan = this.quedan();

                    if (quedan <= 0) {
                        clearInterval(this.temporizador);
                        recargar();
                        return;
                    }

                    if (quedan > cfg.aviso) {
                        this.visible = false;
                        return;
                    }

                    if ((!this.visible || quedan % 10 === 0) && !this.consultando) {
                        await this.sincronizar();
                        quedan = this.quedan();
                        if (quedan > cfg.aviso) {
                            this.visible = false;
                            return;
                        }
                    }

                    this.restante = Math.max(quedan, 0);
                    this.visible = true;
                },

                async sincronizar() {
                    this.consultando = true;
                    try {
                        const respuesta = await fetch(cfg.urlEstado, { headers: cabeceras(), credentials: 'same-origin' });
                        if (respuesta.status === 401) {
                            recargar();
                            return;
                        }
                        if (respuesta.ok) {
                            const datos = await respuesta.json();
                            this.vence = Date.now() + datos.segundos_restantes * 1000;
                        }
                    } catch (e) {
                    } finally {
                        this.consultando = false;
                    }
                },

                async seguir() {
                    try {
                        const respuesta = await fetch(cfg.urlMantener, { method: 'POST', headers: cabeceras(), credentials: 'same-origin' });
                        if (!respuesta.ok) {
                            recargar();
                            return;
                        }
                        const datos = await respuesta.json();
                        this.vence = Date.now() + datos.segundos_restantes * 1000;
                        this.visible = false;
                    } catch (e) {
                        recargar();
                    }
                }
            };
        }
    </script>
@endauth
