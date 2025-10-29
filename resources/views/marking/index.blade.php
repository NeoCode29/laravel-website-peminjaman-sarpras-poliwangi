@extends('layouts.app')

@section('title', 'Daftar Marking')
@section('subtitle', 'Kelola data booking cepat peminjaman sarpras')

@section('header-actions')
@can('peminjaman.create')
<a href="{{ route('marking.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Buat Marking
</a>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/marking.css') }}?v={{ filemtime(public_path('css/marking.css')) }}">
@endpush

@section('content')
<div class="page-content marking-list-page">
    <div class="card card--headerless">
        <div class="card-header" aria-hidden="true"></div>
        <div class="card-main">
            <div class="filters-section">
                <form id="markingFilterForm" method="GET" action="{{ route('marking.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search" class="filter-label">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Cari nama acara, prasarana atau UKM..."
                                       class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label for="status" class="filter-label">Status</label>
                            <select id="status" name="status" class="filter-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Dikonversi</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="start_date" class="filter-label">Mulai</label>
                            <input type="date"
                                   id="start_date"
                                   name="start_date"
                                   value="{{ request('start_date') }}"
                                   class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label for="end_date" class="filter-label">Selesai</label>
                            <input type="date"
                                   id="end_date"
                                   name="end_date"
                                   value="{{ request('end_date') }}"
                                   class="filter-input">
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
                @if($markings->count() > 0)
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Acara</th>
                                    <th>Pembuat</th>
                                    <th>Prasarana/Lokasi</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Jumlah Peserta</th>
                                    <th>Status</th>
                                    <th>Kedaluwarsa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($markings as $marking)
                                <tr>
                                    <td>{{ ($markings->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>
                                        <div class="marking-item">
                                            <div class="marking-item__title">{{ $marking->event_name }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="creator-info">
                                            <div class="creator-name">{{ $marking->user->name ?? '-' }}</div>
                                            <div class="creator-ukm">
                                                @if($marking->ukm)
                                                    <span class="role-badge">{{ $marking->ukm->nama }}</span>
                                                @else
                                                    <span class="text-muted">Tidak ada UKM</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($marking->prasarana)
                                            <span class="role-badge">{{ $marking->prasarana->name }}</span>
                                        @elseif($marking->lokasi_custom)
                                            <span class="role-badge role-badge--secondary">{{ $marking->lokasi_custom }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="user-email">
                                            {{ \Carbon\Carbon::parse($marking->start_datetime)->format('d/m/Y') }} -
                                            {{ \Carbon\Carbon::parse($marking->end_datetime)->format('d/m/Y') }}
                                        </div>
                                        <div class="user-username">
                                            {{ \Carbon\Carbon::parse($marking->start_datetime)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($marking->end_datetime)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $marking->jumlah_peserta }} orang</span>
                                    </td>
                                    <td>
                                        @if($marking->status === 'active')
                                            <span class="status-badge status-active">Aktif</span>
                                        @elseif($marking->status === 'expired')
                                            <span class="status-badge status-inactive">Expired</span>
                                        @elseif($marking->status === 'converted')
                                            <span class="status-badge status-converted">Dikonversi</span>
                                        @else
                                            <span class="status-badge">{{ ucfirst($marking->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($marking->expires_at)
                                            <div class="user-email">{{ \Carbon\Carbon::parse($marking->expires_at)->format('d/m/Y') }}</div>
                                            <div class="user-username">{{ \Carbon\Carbon::parse($marking->expires_at)->format('H:i') }}</div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('marking.show', $marking->id) }}"
                                               class="action-btn action-view"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($marking->user_id === auth()->id() && $marking->status === 'active')
                                            <a href="{{ route('marking.edit', $marking->id) }}"
                                               class="action-btn action-edit"
                                               title="Edit Marking">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button"
                                                    class="action-btn action-delete"
                                                    onclick="deleteMarking({{ $marking->id }})"
                                                    title="Hapus Marking">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif

                                            @can('peminjaman.marking_override')
                                            @if($marking->user_id !== auth()->id())
                                            <button type="button"
                                                    class="action-btn action-reset"
                                                    onclick="overrideMarking({{ $marking->id }})"
                                                    title="Override Marking">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                            @endif
                                            @endcan
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
                                Menampilkan {{ $markings->firstItem() ?? 0 }}-{{ $markings->lastItem() ?? 0 }} dari {{ $markings->total() }} marking
                            </span>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $markings->appends(request()->query())->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <h3 class="empty-title">Belum Ada Marking</h3>
                        <p class="empty-description">
                            @if(request()->filled('search') || request()->filled('status') || request()->filled('start_date') || request()->filled('end_date'))
                                Tidak ada data marking yang sesuai dengan filter yang dipilih.
                            @else
                                Belum ada marking yang dibuat. Buat marking pertama untuk reservasi cepat sarpras.
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('start_date') || request()->filled('end_date'))
                            <a href="{{ route('marking.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hapus Filter
                            </a>
                        @else
                            @can('peminjaman.create')
                            <a href="{{ route('marking.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Buat Marking Pertama
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('marking.partials.modals')
@endsection

@push('scripts')
<script src="{{ asset('js/marking.js') }}?v={{ filemtime(public_path('js/marking.js')) }}"></script>
@endpush
