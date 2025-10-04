@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-nav">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-prev disabled" aria-label="Halaman Sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" 
                       class="pagination-link pagination-prev" 
                       rel="prev" 
                       aria-label="Halaman Sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-dots" aria-hidden="true">
                        <i class="fas fa-ellipsis-h"></i>
                    </span>
                </li>
            @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active">
                                <span class="pagination-link pagination-number active" 
                                      aria-current="page" 
                                      aria-label="Halaman {{ $page }}">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li class="pagination-item">
                                <a href="{{ $url }}" 
                                   class="pagination-link pagination-number" 
                                   aria-label="Halaman {{ $page }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}" 
                       class="pagination-link pagination-next" 
                       rel="next" 
                       aria-label="Halaman Selanjutnya">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-next disabled" aria-label="Halaman Selanjutnya">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
