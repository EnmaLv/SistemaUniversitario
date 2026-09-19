@if ($paginator->hasPages())
    <nav>
        <ul class="rd-pagination">

            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="disabled"><span>&laquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
            @endif

            {{-- Números --}}
            @foreach ($elements as $element)
                {{-- Separador (...) --}}
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @endif

                {{-- Links numéricos --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
                <li class="disabled"><span>&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif

<style>
    .rd-pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        list-style: none;
        padding-left: 0;
        margin-top: 25px;
    }

    .rd-pagination li {
        display: inline-block;
    }

    .rd-pagination a,
    .rd-pagination span {
        display: block;
        padding: 8px 14px;
        font-size: 14px;
        border-radius: 10px;
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-color, #dcdcdc);
        color: var(--text-main, #4a4a4a);
        text-decoration: none;
        font-weight: 500;
        transition: all .2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .rd-pagination a:hover {
        background: var(--color-bg-light-dark-red);
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px var(--color-bg-light-dark-red);
    }

    .rd-pagination .active span {
        background: var(--color-bg-light-dark-red);
        color: white;
        border-color: var(--color-bg-light-dark-red);
        font-weight: 600;
        cursor: default;
        box-shadow: 0 4px 10px var(--color-bg-light-dark-red);
    }

    .rd-pagination .disabled span {
        opacity: 0.4;
        cursor: not-allowed;
        background: var(--input-bg, #f3f3f3);
        color: var(--text-main, #a0a0a0);
    }
</style>
