@php
    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'salud']);
    $avatarBg = $esPsicologia ? 'bg-indigo-600 ring-indigo-400' : 'bg-red-600 ring-red-400';
    $navHoverBg = $esPsicologia ? 'hover:bg-indigo-600/30' : 'hover:bg-[#623739]';
    $focusRing = $esPsicologia ? 'focus:ring-indigo-500' : 'focus:ring-[#dc2626]';
    $dropdownBg = $esPsicologia ? 'bg-slate-900/95 border-indigo-900/60' : 'bg-[#352728]/95 border-[#5c2028]';
    $btnHover = $esPsicologia ? 'hover:bg-indigo-600/20' : 'hover:bg-[#623739]';
    $badgeBg = $esPsicologia ? 'bg-indigo-600' : 'bg-red-600';
    $badgeBorder = $esPsicologia ? 'border-slate-900' : 'border-[#352728]';
    $filterActiveBg = $esPsicologia
        ? 'bg-indigo-600/30 text-white border-indigo-500/50'
        : 'bg-[#623739] text-white border-[#5c2028]';
    $filterInactiveBg = $esPsicologia
        ? 'text-gray-300 hover:bg-indigo-600/20 border-transparent'
        : 'text-gray-300 hover:bg-[#623739] border-transparent';
    $checkIconColor = $esPsicologia ? 'text-indigo-400' : 'text-red-400';
    $itemHoverBg = $esPsicologia ? 'hover:bg-indigo-600/10' : 'hover:bg-[#623739]/40';
    $unreadItemBg = $esPsicologia ? 'bg-indigo-600/15' : 'bg-red-950/30';
    $unreadTimeColor = $esPsicologia ? 'text-indigo-400' : 'text-red-400';
    $unreadDotBg = $esPsicologia ? 'bg-indigo-500' : 'bg-red-500';
    $primaryIconBg = $esPsicologia ? 'bg-indigo-500/20' : 'bg-red-500/20';
    $primaryIconColor = $esPsicologia ? 'text-indigo-400' : 'text-red-400';
    $unreadCount = \App\Models\salud\Notification::obtenerConteoNoLeidas(auth()->id());
    $allNotifications = \App\Models\salud\Notification::obtenerNotificacionesRecientes(auth()->id());
@endphp

<style>
    #top-navbar .breadcrumb {
        background: transparent !important;
        margin-bottom: 0 !important;
        padding: 0 !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        font-size: 0.875rem !important;
        color: rgba(255, 255, 255, 0.7) !important;
    }

    #top-navbar .breadcrumb a {
        color: rgba(255, 255, 255, 0.7) !important;
        text-decoration: none !important;
        transition: var(--trans-default, all 0.2s ease) !important;
    }

    #top-navbar .breadcrumb a:hover {
        color: #ffffff !important;
    }

    #top-navbar .breadcrumb .breadcrumb-item.active {
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    #top-navbar .breadcrumb .breadcrumb-item+.breadcrumb-item::before {
        content: "/" !important;
        padding: 0 0.5rem !important;
        color: rgba(255, 255, 255, 0.4) !important;
    }
</style>

<nav id="top-navbar" x-data="{ open: false }" class="relative z-50 border-b border-white/10">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-3 overflow-hidden">
                <a href="{{ Route::has('home') ? route('home') : url('/') }}"
                    class="flex items-center gap-3 flex-shrink-0">
                    <span class="font-black text-lg text-white tracking-tight">Bienestar Estudiantil</span>
                </a>

                @if (class_exists(\Diglactic\Breadcrumbs\Breadcrumbs::class) && \Diglactic\Breadcrumbs\Breadcrumbs::exists())
                    <span class="text-white/30 font-light hidden md:inline-block select-none">/</span>
                    <nav aria-label="breadcrumb"
                        class="hidden md:flex items-center overflow-x-auto invisible-scrollbar">
                        {!! \Diglactic\Breadcrumbs\Breadcrumbs::render() !!}
                    </nav>
                @endif
            </div>
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <div class="relative" x-data="{ openNotif: false, optionsOpen: false, filter: 'all' }" @click.away="openNotif = false; optionsOpen = false">
                    <button @click="openNotif = !openNotif"
                        class="relative p-2 text-gray-200 hover:text-white bg-white/10 {{ $btnHover }} rounded-full transition-all duration-200 focus:outline-none focus:ring-2 {{ $focusRing }}"
                        title="Notificaciones">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                        @if ($unreadCount > 0)
                            <span id="main-notif-badge"
                                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-0.5 flex items-center justify-center rounded-full {{ $badgeBg }} text-white text-[10px] font-bold border-2 {{ $badgeBorder }} shadow">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <div x-show="openNotif" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        class="absolute -right-12 sm:right-0 mt-3 w-[320px] max-w-[95vw] sm:w-[380px] {{ $dropdownBg }} backdrop-blur-md rounded-2xl shadow-2xl border z-50 overflow-hidden"
                        style="display: none;">

                        <div class="px-4 pt-4 pb-2 flex items-center justify-between">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Notificaciones</h3>
                            <div class="relative">
                                <button @click.stop="optionsOpen = !optionsOpen"
                                    class="p-1.5 text-gray-400 hover:text-white {{ $btnHover }} rounded-full transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                    </svg>
                                </button>
                                <div x-show="optionsOpen" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute right-0 mt-1 w-56 {{ $dropdownBg }} backdrop-blur-md rounded-xl shadow-xl border z-50 overflow-hidden"
                                    style="display: none;">
                                    <button type="button"
                                        @click.stop="
                                                fetch('{{ route('notifications.readAll') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    }
                                                }).then(() => {
                                                    document.querySelectorAll('[data-unread=\'true\']').forEach(el => {
                                                        el.dataset.unread = 'false';
                                                        el.className = el.className.replace(/bg-(indigo|red|blue)-\S+/g, '');
                                                        const dot = el.querySelector('.notif-dot');
                                                        if (dot) dot.remove();
                                                        const time = el.querySelector('.notif-time');
                                                        if (time) { 
                                                            time.classList.remove('text-blue-600', 'text-indigo-600', 'text-red-600', 'text-indigo-400', 'text-red-400', 'font-bold'); 
                                                            time.classList.add('text-gray-400'); 
                                                        }
                                                        const body = el.querySelector('.notif-body');
                                                        if (body) body.classList.remove('font-semibold');
                                                    });
                                                    document.querySelectorAll('.notif-badge').forEach(b => b.remove());
                                                    const mb = document.getElementById('main-notif-badge');
                                                    if(mb) mb.remove();
                                                    optionsOpen = false;
                                                })
                                            "
                                        class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:text-white {{ $btnHover }} transition text-left">
                                        <svg class="w-4 h-4 {{ $checkIconColor }} flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Marcar todo como leído
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 pb-3 flex gap-2">
                            <button @click.stop="filter = 'all'"
                                :class="filter === 'all' ? '{{ $filterActiveBg }}' : '{{ $filterInactiveBg }}'"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap border">
                                Todas
                            </button>
                            <button @click.stop="filter = 'unread'"
                                :class="filter === 'unread' ? '{{ $filterActiveBg }}' : '{{ $filterInactiveBg }}'"
                                class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap border flex items-center gap-1.5">
                                No leídas
                                @if ($unreadCount > 0)
                                    <span
                                        class="{{ $badgeBg }} text-white rounded-full px-1.5 py-0.5 text-[9px] notif-badge">{{ $unreadCount }}</span>
                                @endif
                            </button>
                        </div>

                        <div x-ref="notifList"
                            class="custom-scrollbar max-h-[400px] overflow-y-auto divide-y divide-white/5 border-t border-white/10">
                            @forelse($allNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                    data-notif="{{ $notification->id }}"
                                    data-unread="{{ is_null($notification->read_at) ? 'true' : 'false' }}"
                                    x-show="filter === 'all' || (filter === 'unread' && {{ is_null($notification->read_at) ? 'true' : 'false' }})"
                                    class="block px-4 py-3 {{ $itemHoverBg }} transition group relative {{ is_null($notification->read_at) ? $unreadItemBg : '' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="relative flex-shrink-0">
                                            @if (($notification->data['type_id'] ?? '') === 'new_message')
                                                <div
                                                    class="w-10 h-10 {{ $primaryIconBg }} rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 {{ $primaryIconColor }}" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_confirmed')
                                                <div
                                                    class="w-10 h-10 bg-emerald-500/20 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-emerald-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18" rx="2"
                                                            ry="2" stroke-width="2" />
                                                        <line x1="16" y1="2" x2="16"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="8" y1="2" x2="8"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="3" y1="10" x2="21"
                                                            y2="10" stroke-width="2" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 16l2 2 4-4" />
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_cancelled')
                                                <div
                                                    class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-red-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18"
                                                            rx="2" ry="2" stroke-width="2" />
                                                        <line x1="16" y1="2" x2="16"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="8" y1="2" x2="8"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="3" y1="10" x2="21"
                                                            y2="10" stroke-width="2" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M10 14l4 4M14 14l-4 4" />
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'cita_requested')
                                                <div
                                                    class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-amber-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <rect x="3" y="4" width="18" height="18"
                                                            rx="2" ry="2" stroke-width="2" />
                                                        <line x1="16" y1="2" x2="16"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="8" y1="2" x2="8"
                                                            y2="6" stroke-width="2" stroke-linecap="round" />
                                                        <line x1="3" y1="10" x2="21"
                                                            y2="10" stroke-width="2" />
                                                        <circle cx="12" cy="16" r="3"
                                                            stroke-width="1.5" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5" d="M12 14.5v1.5l1 1" />
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'nuevo_aviso')
                                                <div
                                                    class="w-10 h-10 {{ $primaryIconBg }} rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 {{ $primaryIconColor }}" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                                        </path>
                                                    </svg>
                                                </div>
                                            @elseif(($notification->data['type_id'] ?? '') === 'reaccion_aviso')
                                                <div
                                                    class="w-10 h-10 bg-pink-500/20 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-pink-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5">
                                                        </path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div
                                                    class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-300" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="notif-body text-[13px] text-gray-200 leading-snug {{ is_null($notification->read_at) ? 'font-semibold' : '' }}">
                                                {{ $notification->data['body'] ?? '' }}
                                            </p>
                                            <p
                                                class="notif-time text-[11px] mt-0.5 {{ is_null($notification->read_at) ? "$unreadTimeColor font-bold" : 'text-gray-400' }}">
                                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                        @if (is_null($notification->read_at))
                                            <div
                                                class="notif-dot w-2.5 h-2.5 {{ $unreadDotBg }} rounded-full flex-shrink-0 self-center mt-0.5">
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="py-10 flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10 text-gray-500/50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                        </path>
                                    </svg>
                                    <p class="text-xs font-medium">Sin notificaciones</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @if (View::exists('components.theme-switcher'))
                    <x-theme-switcher />
                @endif
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="group flex items-center gap-3 px-3 py-1.5 rounded-full {{ $navHoverBg }} transition-all border border-white/10 focus:outline-none">
                            <div class="text-right">
                                <div class="text-sm font-semibold text-white leading-tight">
                                    {{ Auth::user()?->persona?->nombre_persona ?? (Auth::user()?->nombres ?? Auth::user()?->name ?? 'Usuario') }}
                                </div>
                                <div class="text-xs text-gray-300 leading-tight">
                                    {{ ucfirst(Auth::user()->role ?? 'Usuario') }}
                                </div>
                            </div>

                            <div class="flex-shrink-0">
                                @php
                                    $user = Auth::user();
                                    $initials = strtoupper(mb_substr($user?->name ?? ($user?->nombres ?? 'U'), 0, 1));
                                @endphp
                                <div
                                    class="h-9 w-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-sm font-black shadow-md ring-2">
                                    {{ $initials }}
                                </div>
                            </div>

                            <svg class="h-4 w-4 text-gray-300 group-hover:text-white" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (Route::has('profile.edit'))
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Perfil') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden gap-1">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white {{ $navHoverBg }}">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
