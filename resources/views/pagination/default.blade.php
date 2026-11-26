@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <p class="pagination-info">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <strong>{{ $paginator->firstItem() }}</strong>
                {!! __('to') !!}
                <strong>{{ $paginator->lastItem() }}</strong>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <strong>{{ $paginator->total() }}</strong>
            {!! __('results') !!}
        </p>

        <div class="pagination">
            @if ($paginator->onFirstPage())
                <span class="pagination-link disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    &lsaquo;
                </span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    &lsaquo;
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-link disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    &rsaquo;
                </a>
            @else
                <span class="pagination-link disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    &rsaquo;
                </span>
            @endif
        </div>
    </nav>
@endif
