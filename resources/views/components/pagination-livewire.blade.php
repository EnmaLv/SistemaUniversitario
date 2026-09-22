@props(['paginator'])

@if ($paginator->hasPages())
    <nav>
        <ul class="rd-pagination">

            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="disabled"><span>&laquo;</span></li>
            @else
                <li><a type="button" wire:click="gotoPage({{ $paginator->currentPage() - 1 }})" rel="prev">&laquo;</a>
                </li>
            @endif

            {{-- Numeros --}}
            @foreach ($elements as $element)
                {{-- Separador (...) --}}
                @if (is_string($element))
                    <li class="disabled">
                        <span>{{ $element }}</span>
                    </li>
                @endif

                {{-- Links numéricos --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active">
                                <span>{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a type="button" wire:click="gotoPage({{ $page }})">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a type="button" wire:click="gotoPage({{ $paginator->currentPage() + 1 }})"
                        rel="next">&raquo;</a>
                </li>
            @else
                <li class="disabled">
                    <span>&raquo;</span>
                </li>
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
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        text-decoration: none;
        font-weight: 500;
        transition: all .2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .rd-pagination a:hover {
        background: var(--color-primary);
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px var(--color-primary-alpha);
    }

    .rd-pagination .active span {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
        font-weight: 600;
        cursor: default;
        box-shadow: 0 4px 10px var(--color-primary-alpha);
    }

    .rd-pagination .disabled span {
        opacity: 0.4;
        cursor: not-allowed;
        background: var(--input-bg);
        color: var(--text-muted, var(--text-main));
    }
</style>
