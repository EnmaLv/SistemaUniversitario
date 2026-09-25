@php
    $moduloActivo = session('modulo_activo', 'general');
    $isPsico = $esPsicologia ?? in_array($moduloActivo, ['psicologia', 'salud']);
    $sidebarHover = $isPsico ? 'hover:bg-indigo-600/30' : 'hover:bg-[#623739]';
    $btnSelectBg = $isPsico
        ? 'bg-indigo-600/20 hover:bg-indigo-600/40 border-indigo-500/30'
        : 'bg-white/20 hover:bg-[#623739] border-white/20';
@endphp

<aside id="main-sidebar" :class="sidebarOpen ? 'w-64' : 'w-20'"
    class="hidden lg:flex lg:flex-col h-full border-r shadow-sm py-3 flex-shrink-0 transition-all duration-300 ease-in-out overflow-y-auto invisible-scrollbar z-20 relative"
    x-data="{ activeSection: '{{ request()->segment(2) ?? request()->segment(1) }}' }">

    <div class="flex items-center px-3 h-12 mb-2" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
        <a href="{{ Route::has('home') ? route('home') : url('/') }}"
            class="flex items-center gap-3 transition-colors overflow-hidden group rounded-lg p-1.5 {{ $sidebarHover }}"
            :class="sidebarOpen ? 'flex' : 'hidden'" title="Inicio">
            <div
                class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-white flex-shrink-0 group-hover:bg-white/20 transition-all">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-5.5a1.5 1.5 0 0 0-3 0V21H4a1 1 0 0 1-1-1V9.5z" />
                </svg>
            </div>
            <span class="font-bold text-sm text-white whitespace-nowrap">Inicio</span>
        </a>

        <button @click="sidebarOpen = !sidebarOpen"
            class="h-8 w-8 flex items-center justify-center rounded-lg text-white/80 hover:text-white {{ $sidebarHover }} transition-colors flex-shrink-0">
            <svg class="h-4 w-4 transition-transform duration-300" :class="sidebarOpen ? 'rotate-180' : ''"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>

    @if (session('modulo_activo'))
        <div class="mx-3 mb-2 px-3 py-1.5 rounded-lg bg-black/20 border border-white/10 flex items-center justify-between"
            :class="sidebarOpen ? 'flex' : 'hidden'">
            <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider truncate">
                Módulo: <span class="text-white">{{ ucfirst(session('modulo_activo')) }}</span>
            </span>
            <a href="{{ route('admin.modulos.seleccionar') }}" class="text-white/80 hover:text-white transition-colors"
                title="Cambiar de módulo">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </a>
        </div>
    @endif

    <div class="mx-3 mb-3 border-t border-white/15"></div>

    <nav class="flex flex-col gap-1 px-3 flex-1">
        @canModule('comedor')
        @includeIf('layouts.sidebar.comedor')
        @endcanModule

        @canModule('transporte')
        @includeIf('layouts.sidebar.transporte')
        @endcanModule

        @canModule('salud')
        @includeIf('layouts.sidebar.salud')
        @endcanModule

        @canModule('psicologia')
        @includeIf('layouts.sidebar.psicologia')
        @endcanModule

        @canModule('beca')
        @includeIf('layouts.sidebar.becas')
        @endcanModule

        @canModule('administracion')
        @includeIf('layouts.sidebar.administracion')
        @endcanModule

        @if (!session('modulo_activo'))
            <div class="p-3 text-center" :class="sidebarOpen ? 'block' : 'hidden'">
                <p class="text-xs text-white/70 mb-2">Ningún módulo activo</p>
                <a href="{{ route('admin.modulos.seleccionar') }}"
                    class="inline-block text-xs font-bold text-white {{ $btnSelectBg }} px-3 py-1.5 rounded-lg transition-colors border">
                    Seleccionar Módulo
                </a>
            </div>
        @endif

            <div class="mt-auto px-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" @if (!request()->routeIs('chat.*')) @click="$dispatch('toggle-chat')" @endif
                    class="group flex items-center gap-3 h-11 w-full rounded-xl transition-all duration-200 relative"
                    :class="[
                        (isChatOpen || {{ request()->routeIs('chat.*') ? 'true' : 'false' }}) ?
                        'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm' :
                        'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-300',
                        sidebarOpen ? 'px-3' : 'justify-center px-0'
                    ]"
                    title="Mensajes">
                    <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span
                        class="sidebar-text text-sm font-medium whitespace-nowrap overflow-hidden transition-all duration-300"
                        :class="sidebarOpen ? 'opacity-100 max-w-[160px]' : 'opacity-0 max-w-0'">Mensajes</span>
                    @php $unreadMsgs = \App\Models\Usuario::contarMensajesNoLeidos(auth()->id()); @endphp
                    <span
                        class="chat-badge absolute -top-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold border-2 border-white dark:border-gray-800 shadow"
                        data-count="{{ $unreadMsgs }}" style="{{ $unreadMsgs > 0 ? '' : 'display: none;' }}">
                        {{ $unreadMsgs > 99 ? '99+' : $unreadMsgs }}
                    </span>
                </button>
            </div>

            <script>
                // ─── Helpers globales para el badge ───
                window.updateChatBadge = function (count) {
                    const badge = document.querySelector('.chat-badge');
                    if (!badge) return;
                    if (count === undefined) {
                        count = parseInt(badge.dataset.count || '0', 10);
                    }
                    badge.dataset.count = count;
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? '' : 'none';
                };

                window.incrementChatBadge = function (by = 1) {
                    const badge = document.querySelector('.chat-badge');
                    if (!badge) return;
                    const current = parseInt(badge.dataset.count || '0', 10);
                    window.updateChatBadge(current + by);
                };

                window.recalculateChatBadge = function (contacts) {
                    if (!Array.isArray(contacts)) return;
                    const total = contacts.reduce((sum, c) => sum + (c.unreadCount || 0), 0);
                    window.updateChatBadge(total);
                };

                // ─── Listener de mensajes nuevos ───
                document.addEventListener('DOMContentLoaded', () => {
                    if (!window.Echo || !{{ auth()->id() ?? 'null' }}) return;

                    window.Echo.private('App.Models.Usuario.' + {{ auth()->id() ?? 'null' }})
                        .listen('.MessageSent', (e) => {
                            // Solo incrementar si el chat con ese contacto NO está abierto
                            // El chat-window/chat-index llamará a recalculateChatBadge cuando corresponda
                            window.incrementChatBadge();
                        });
                });
            </script>
    </nav>
</aside>
