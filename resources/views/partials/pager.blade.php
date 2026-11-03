@if ($paginator->hasPages())
    <nav class="pager" aria-label="Páginas">
        @if ($paginator->onFirstPage())
            <span class="off">Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">Anterior</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="off">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="on">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Siguiente</a>
        @else
            <span class="off">Siguiente</span>
        @endif
    </nav>
@endif
