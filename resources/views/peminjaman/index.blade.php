@extends('layouts.app')

@section('title', 'Daftar Peminjaman')
@section('subtitle', 'Kelola pengajuan peminjaman sarana/prasarana')

@section('header-actions')
<a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Ajukan Peminjaman
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/peminjaman.css') }}?v={{ filemtime(public_path('css/peminjaman.css')) }}">
@endpush

@section('content')
<div class="page-content">
    <div class="card card--headerless">
        <div class="card-main">
            <div class="filters-section">
                <form id="filterForm" method="GET" action="{{ route('peminjaman.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search" class="filter-label">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ request('search', request('q')) }}"
                                       placeholder="Cari nama acara atau peminjam..."
                                       class="search-input">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="status" class="filter-label">Status</label>
                            <select id="status" name="status" class="filter-select">
                                <option value="">Semua Status</option>
                                @foreach(["pending","approved","rejected","picked_up","returned","cancelled"] as $st)
                                    <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="start_date" class="filter-label">Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="filter-input">
                        </div>
                        <div class="filter-group">
                            <label for="end_date" class="filter-label">Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="filter-input">
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
                @if(($peminjaman ?? null) && $peminjaman->count() > 0)
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Acara</th>
                                    <th>Peminjam</th>
                                    <th>Periode & Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peminjaman as $index => $row)
                                    @php
                                        $startDate = $row->start_date ? \Carbon\Carbon::parse($row->start_date) : null;
                                        $endDate = $row->end_date ? \Carbon\Carbon::parse($row->end_date) : null;
                                        $startTime = $row->start_time ? \Carbon\Carbon::parse($row->start_time) : null;
                                        $endTime = $row->end_time ? \Carbon\Carbon::parse($row->end_time) : null;

                                        $dateRange = $startDate
                                            ? $startDate->format('d/m/Y') . ($endDate && !$endDate->isSameDay($startDate)
                                                ? ' - ' . $endDate->format('d/m/Y')
                                                : '')
                                            : '-';

                                        $timeRange = $startTime
                                            ? $startTime->format('H:i') . ($endTime ? ' - ' . $endTime->format('H:i') : '')
                                            : null;

                                        $location = $row->prasarana->name ?? $row->lokasi_custom ?? '-';
                                        $participantCount = $row->jumlah_peserta ? number_format($row->jumlah_peserta) : null;
                                    @endphp
                                    <tr class="table-row" data-row="{{ $index + 1 }}">
                                        <td class="table-number" data-label="No.">
                                            {{ $peminjaman->firstItem() + $index }}
                                        </td>
                                        <td data-label="Acara">
                                            <div class="event-info">
                                                <div class="event-icon">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div class="event-details">
                                                    <div class="event-name">{{ $row->event_name ?? '-' }}</div>
                                                    <div class="event-meta">
                                                        @if($participantCount)
                                                            <span class="event-meta-item">
                                                                <i class="fas fa-users"></i>
                                                                {{ $participantCount }} peserta
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Peminjam">
                                            <div class="borrower-info">
                                                <div class="borrower-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="borrower-details">
                                                    <div class="borrower-name">{{ $row->user->name ?? '-' }}</div>
                                                    @if(optional($row->user)->email)
                                                        <div class="borrower-email">{{ $row->user->email }}</div>
                                                    @endif
                                                    @if(optional($row->user)->phone)
                                                        <div class="borrower-phone">{{ $row->user->phone }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Periode & Lokasi">
                                            <div class="schedule-info">
                                                <div class="schedule-item">
                                                    <i class="fas fa-calendar-day"></i>
                                                    <span>{{ $dateRange }}</span>
                                                </div>
                                                @if($timeRange)
                                                    <div class="schedule-item">
                                                        <i class="fas fa-clock"></i>
                                                        <span>{{ $timeRange }}</span>
                                                    </div>
                                                @endif
                                                <div class="schedule-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>{{ $location }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            @php($badge = $row->display_status_badge)
                                            <span class="status-badge {{ $badge['class'] ?? ('status-' . ($row->status ?? 'pending')) }}">
                                                {{ $badge['label'] ?? ucfirst(str_replace('_',' ', $row->status ?? 'pending')) }}
                                            </span>
                                            @if($row->konflik)
                                                <span class="badge badge--override" title="Sedang konflik peminjaman">
                                                    <i class="fas fa-undo"></i>
                                                    Konflik
                                                </span>
                                            @endif
                                        </td>
                                        <td data-label="Aksi">
                                            <div class="action-buttons">
                                                <a href="{{ route('peminjaman.show', $row->id) }}" class="action-btn action-view" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-section">
                        <div class="pagination-info">
                            <span class="pagination-text">
                                Menampilkan {{ $peminjaman->firstItem() }}-{{ $peminjaman->lastItem() }} dari {{ $peminjaman->total() }} peminjaman
                            </span>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $peminjaman->appends(request()->query())->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="empty-title">Belum ada peminjaman</h3>
                        <p class="empty-description">
                            @if(request()->filled('search') || request()->filled('status') || request()->filled('start_date') || request()->filled('end_date'))
                                Tidak ada peminjaman yang sesuai dengan filter yang dipilih.
                            @else
                                Mulai dengan mengajukan peminjaman baru.
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('start_date') || request()->filled('end_date'))
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hapus Filter
                            </a>
                        @else
                            <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Ajukan Peminjaman
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/peminjaman.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filter values change
    const filterForm = document.getElementById('filterForm');
    const filterInputs = filterForm.querySelectorAll('input, select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Add small delay to prevent too many requests
            setTimeout(() => {
                filterForm.submit();
            }, 300);
        });
    });
    
    // For search input, use input event for real-time search
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 500); // 500ms delay for search
    });
    
    // Desktop table row click effects only
    const tableRows = document.querySelectorAll('.table-row');
    
    // Only add click functionality for desktop
    if (window.innerWidth > 768) {
        tableRows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.action-buttons')) {
                    return;
                }
                
                // Navigate to detail page
                const viewLink = this.querySelector('.action-view');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            });
        });
    }
});
</script>
@endpush

@push('styles')
<style>
/* Filters Section */
.filters-section {
    margin-bottom: 24px;
}

.filters-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e0e0e0;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 14px;
    font-weight: 500;
    color: #333333;
    margin: 0;
}

.search-input {
    width: 100%;
    background: #ffffff;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
    color: #1f2937;
    height: 44px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.search-input::placeholder {
    color: #98a2b3;
}

.filter-select,
.filter-input {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px 36px 10px 12px;
    min-width: 120px;
    height: 40px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    font-size: 14px;
    color: #333333;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-input:focus,
.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
}

.search-input:hover,
.filter-select:hover,
.filter-input:hover {
    border-color: #c3c9d4;
    background-color: #fcfdff;
    box-shadow: 0 2px 6px rgba(16, 24, 40, 0.08);
}

/* Table Section */
.table-section {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    border-collapse: collapse;
    width: 100%;
    min-width: 900px;
}

.data-table thead {
    background: #f5f7fa;
}

.data-table th {
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #333333;
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    white-space: nowrap;
    min-width: 150px;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
}

.data-table th:first-child,
.data-table td:first-child {
    min-width: 70px;
    width: 70px;
    text-align: center;
}

.data-table th:nth-child(2),
.data-table td:nth-child(2) {
    min-width: 220px;
}

.data-table th:nth-child(3),
.data-table td:nth-child(3) {
    min-width: 200px;
}

.data-table th:nth-child(4),
.data-table td:nth-child(4) {
    min-width: 220px;
}

.data-table th:nth-child(5),
.data-table td:nth-child(5) {
    min-width: 140px;
}

.data-table th:nth-child(6),
.data-table td:nth-child(6) {
    min-width: 120px;
}

.data-table th:last-child {
    text-align: center;
}

.data-table td:last-child {
    text-align: center;
}

.table-number {
    font-weight: 600;
    color: #1f2937;
}

.data-table tbody tr:hover {
    background-color: #fafafa;
}

/* Event & Borrower Info */
.event-info,
.borrower-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.event-icon,
.borrower-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666666;
    font-size: 16px;
    flex-shrink: 0;
}

.event-details,
.borrower-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.event-name,
.borrower-name {
    font-weight: 500;
    color: #333333;
}

.event-meta,
.borrower-email,
.borrower-phone {
    font-size: 12px;
    color: #666666;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.event-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Schedule Info */
.schedule-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.schedule-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #555555;
    font-size: 13px;
}

.schedule-item i {
    color: #666666;
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
}

.status-badge.status-pending { background: #fff3e0; color: #f57c00; }
.status-badge.status-approved { background: #e8f5e8; color: #2e7d32; }
.status-badge.status-rejected { background: #ffebee; color: #c62828; }
.status-badge.status-picked_up { background: #e1f5fe; color: #0277bd; }
.status-badge.status-returned { background: #e8f5e8; color: #2e7d32; }
.status-badge.status-cancelled { background: #f5f5f5; color: #6c757d; }

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.action-btn {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #333333;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    font-size: 14px;
    aspect-ratio: 1 / 1;
}

.action-btn i {
    pointer-events: none;
}

.action-btn:focus-visible {
    outline: none;
    border-color: #b6d4fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.action-btn:hover {
    background: #f5f5f5;
    border-color: #cccccc;
    transform: translateY(-1px);
}

.action-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.18);
}

.action-view:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

@media (max-width: 768px) {
    .action-buttons {
        gap: 8px;
        justify-content: center;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        font-size: 13px;
    }

    .data-table td:last-child {
        text-align: center;
    }
}

/* Pagination */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 16px;
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    border-radius: 0 0 8px 8px;
}

.pagination-info {
    flex: 1;
}

.pagination-text {
    font-size: 14px;
    color: #666666;
    font-weight: 400;
}

.pagination-controls {
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

.pagination-nav {
    display: flex;
    justify-content: center;
    align-items: center;
}

.pagination {
    display: flex;
    gap: 4px;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pagination-item {
    display: flex;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #333333;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pagination-link:hover:not(.disabled) {
    background: #f5f5f5;
    border-color: #cccccc;
}

.pagination-link:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    border-color: #007bff;
}

.pagination-link.active {
    background: #333333;
    color: #ffffff;
    border-color: #333333;
}

.pagination-link.disabled {
    background: #f5f5f5;
    color: #999999;
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination-link.disabled:hover {
    background: #f5f5f5;
    border-color: #e0e0e0;
}

.pagination-number {
    min-width: 32px;
}

.pagination-dots {
    background: transparent;
    border: none;
    color: #999999;
    min-width: 32px;
}

.pagination-dots:hover {
    background: transparent;
}

.pagination i {
    font-size: 12px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 48px 20px;
}

.empty-icon {
    color: #6c757d;
    margin-bottom: 16px;
}

.empty-icon i {
    font-size: 48px;
}

.empty-title {
    font-size: 20px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 8px;
}

.empty-description {
    font-size: 14px;
    color: #666666;
    margin-bottom: 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Responsive */
@media (max-width: 1024px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
}


@media (max-width: 768px) {
    .filters-form {
        padding: 16px;
    }

    .filters-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .pagination-section {
        flex-direction: column;
        gap: 16px;
        text-align: center;
        padding: 16px;
    }

    .table-wrapper {
        margin: 0 -16px;
        padding: 0 16px;
    }

    .data-table {
        min-width: 800px;
    }

    .event-icon,
    .borrower-avatar {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .filters-section {
        margin-bottom: 20px;
    }

    .table-wrapper {
        margin: 0 -12px;
        padding: 0 12px;
    }

    .data-table {
        min-width: 720px;
    }

    .empty-state {
        padding: 32px 16px;
    }

    .empty-icon i {
        font-size: 36px;
    }

    .empty-title {
        font-size: 18px;
    }

    .empty-description {
        font-size: 13px;
    }
}
</style>
@endpush
