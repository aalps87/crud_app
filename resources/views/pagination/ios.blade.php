@if ($paginator->hasPages())
    <nav class="pager" aria-label="Paginação">
        @if ($paginator->onFirstPage())
            <span class="pager-button is-disabled" aria-disabled="true"><x-icon name="chevron-left" :size="18" :stroke="2.3" /><span class="sr-only">Página anterior</span></span>
        @else
            <a class="pager-button pressable" href="{{ $paginator->previousPageUrl() }}" rel="prev" data-vt="back"><x-icon name="chevron-left" :size="18" :stroke="2.3" /><span class="sr-only">Página anterior</span></a>
        @endif

        <span class="pager-dots" aria-label="Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}">
            @if ($paginator->lastPage() <= 7)
                @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                    <a href="{{ $paginator->url($page) }}" class="pager-dot {{ $page === $paginator->currentPage() ? 'is-current' : '' }}" data-vt="{{ $page < $paginator->currentPage() ? 'back' : 'forward' }}" aria-label="Página {{ $page }}" @if($page === $paginator->currentPage()) aria-current="page" @endif></a>
                @endfor
            @endif
            <span class="pager-label">{{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
        </span>

        @if ($paginator->hasMorePages())
            <a class="pager-button pressable" href="{{ $paginator->nextPageUrl() }}" rel="next"><x-icon name="chevron-right" :size="18" :stroke="2.3" /><span class="sr-only">Próxima página</span></a>
        @else
            <span class="pager-button is-disabled" aria-disabled="true"><x-icon name="chevron-right" :size="18" :stroke="2.3" /><span class="sr-only">Próxima página</span></span>
        @endif
    </nav>
@endif
