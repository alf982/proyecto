@props(['paginator' => null, 'query' => null])

@php
    $items = $paginator ?? $query;
@endphp

@if ($items && $items->hasPages())
    <div class="pagination">
        @foreach($items->links()->elements as $element)
            @if(is_string($element))
                <span class="page-link" style="opacity:.4;">{{ $element }}</span>
            @elseif(is_array($element))
                @foreach($element as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page == $items->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
            @endif
        @endforeach
        <span class="page-info">
            {{ $items->firstItem() }}–{{ $items->lastItem() }} de {{ $items->total() }}
        </span>
    </div>
@endif
