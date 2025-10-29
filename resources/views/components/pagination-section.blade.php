@props([
    'paginator',
    'itemLabel' => 'item',
])

@if($paginator && $paginator->count())
<div class="pagination-section">
    <div class="pagination-info">
        <span class="pagination-text">
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} {{ $itemLabel }}
        </span>
    </div>
    <div class="pagination-controls">
        <div class="pagination-wrapper">
            {{ $paginator->links('pagination.custom') }}
        </div>
    </div>
</div>
@endif
