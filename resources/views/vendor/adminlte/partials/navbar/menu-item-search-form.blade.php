<li class="nav-item">

    {{-- Search toggle button --}}
    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
        <i class="fas fa-search"></i>
    </a>

    {{-- Search bar --}}
    <div class="navbar-search-block">
        <form class="form-inline" action="{{ $item['href'] }}" method="{{ $item['method'] }}">
            {{ csrf_field() }}

            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50">

                {{-- Search input --}}
                <input class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input-navbar" type="search"
                    @isset($item['id']) id="{{ $item['id'] }}" @endisset
                    name="{{ $item['input_name'] }}"
                    placeholder="{{ $item['text'] }}"
                    aria-label="{{ $item['text'] }}">

                {{-- Search buttons --}}
                <div class="">
                    <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 navbar" type="button" data-widget="navbar-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>

</li>
