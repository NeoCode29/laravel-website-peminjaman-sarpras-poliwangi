@extends('layouts.app')

@section('title', 'Detail Kategori Prasarana')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kategori-prasarana.css') }}?v={{ filemtime(public_path('css/kategori-prasarana.css')) }}">
@endpush

@section('content')
<section class="detail-page">
    <div class="card category-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-building detail-icon"></i>
                <h2 class="card-title">{{ $kategoriPrasarana->name }}</h2>
                @if($kategoriPrasarana->is_active ?? true)
                    <span class="status-badge status-approved">Aktif</span>
                @else
                    <span class="status-badge status-rejected">Tidak Aktif</span>
                @endif
            </div>
            <div class="card-header__actions">
                <span class="meta-chip">
                    <i class="fas fa-layer-group"></i>
                    {{ $kategoriPrasarana->prasarana_count ?? 0 }} Prasarana
                </span>
            </div>
        </div>
        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-toggle-on"></i>
                    {{ ($kategoriPrasarana->is_active ?? true) ? 'Status Aktif' : 'Status Tidak Aktif' }}
                </div>
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i>
                    Dibuat {{ $kategoriPrasarana->created_at->format('d M Y, H:i') }}
                </div>
                <div class="chip">
                    <i class="fas fa-history"></i>
                    Diupdate {{ $kategoriPrasarana->updated_at->format('d M Y, H:i') }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Kategori</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Kategori</span>
                            <span class="detail-value detail-value--start">{{ $kategoriPrasarana->name }}</span>
                        </div>
                        <div class="detail-row detail-row--column">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-value--start">
                                @if($kategoriPrasarana->description)
                                    {{ $kategoriPrasarana->description }}
                                @else
                                    <span class="text-muted">Tidak ada deskripsi</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Status & Statistik</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                @if($kategoriPrasarana->is_active ?? true)
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="status-badge status-rejected">
                                        <i class="fas fa-times-circle"></i>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Prasarana</span>
                            <span class="detail-value">
                                <span class="status-badge status-approved">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $kategoriPrasarana->prasarana_count ?? 0 }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Dibuat Pada</span>
                            <span class="detail-value detail-value--start">{{ $kategoriPrasarana->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diupdate</span>
                            <span class="detail-value detail-value--start">{{ $kategoriPrasarana->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    <a href="{{ route('kategori-prasarana.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                    @can('sarpras.edit')
                    <a href="{{ route('kategori-prasarana.edit', $kategoriPrasarana->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Kategori
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if($kategoriPrasarana->prasarana && $kategoriPrasarana->prasarana->count() > 0)
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h3 class="card-title">
                        <i class="fas fa-list card-title-icon"></i>
                        Daftar Prasarana
                    </h3>
                    <p class="card-subtitle">Prasarana yang menggunakan kategori ini</p>
                </div>
            </div>
        </div>
        
        <div class="card-main">
            <div class="table-section">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table-head" style="width: 200px;">Nama Prasarana</th>
                                <th class="table-head" style="width: 200px;">Lokasi</th>
                                <th class="table-head" style="width: 100px;">Kapasitas</th>
                                <th class="table-head" style="width: 100px;">Status</th>
                                <th class="table-head" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoriPrasarana->prasarana as $prasarana)
                            <tr>
                                <td class="table-body">
                                    <div class="user-info">
                                        <div class="user-details">
                                            <div class="user-name">{{ $prasarana->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-body">
                                    <div class="description-cell">
                                        @if($prasarana->lokasi)
                                            <div class="description-text" title="{{ $prasarana->lokasi }}">
                                                {{ Str::limit($prasarana->lokasi, 50) }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="table-body">
                                    <span class="status-badge status-approved">{{ $prasarana->kapasitas ?? '-' }}</span>
                                </td>
                                <td class="table-body">
                                    @if($prasarana->status === 'tersedia')
                                        <span class="status-badge status-approved">Tersedia</span>
                                    @elseif($prasarana->status === 'rusak')
                                        <span class="status-badge status-rejected">Rusak</span>
                                    @else
                                        <span class="status-badge status-pending">Tidak Tersedia</span>
                                    @endif
                                </td>
                                <td class="table-body">
                                    <div class="action-buttons">
                                        @can('sarpras.view')
                                        <a href="{{ route('prasarana.show', $prasarana->id) }}" 
                                           class="action-button action-button-view" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('sarpras.edit')
                                        <a href="{{ route('prasarana.edit', $prasarana->id) }}" 
                                           class="action-button action-button-edit" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-main">
            <div class="empty-state">
                <div class="empty-state-container">
                    <i class="fas fa-building empty-state-icon"></i>
                    <h3 class="empty-state-title">Belum Ada Prasarana</h3>
                    <p class="empty-state-description">
                        Kategori ini belum memiliki prasarana yang terkait.
                        @can('sarpras.create')
                            <a href="{{ route('prasarana.create') }}" class="text-primary">Buat prasarana baru</a> untuk menggunakan kategori ini.
                        @endcan
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection

@push('styles')
<style>
.detail-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.category-detail-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.card-header__title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.card-header__title .card-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #333333;
}

.detail-icon {
    font-size: 24px;
    color: #4b5563;
}

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
}

.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    color: #333333;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 500;
}

.card-main {
    padding: 20px;
}

.summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    color: #333333;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
}

.chip i {
    color: #6b7280;
}

.detail-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.detail-block {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-row--column {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
}

.detail-label {
    font-size: 13px;
    color: #6b7280;
    min-width: 140px;
}

.detail-value {
    font-size: 14px;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    text-align: right;
}

.detail-value--start {
    justify-content: flex-start;
    text-align: left;
}

.detail-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.detail-actions .btn {
    min-width: 140px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 500;
    border-radius: 6px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
}

.status-badge.status-approved {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.status-pending {
    background: #fff3e0;
    color: #f57c00;
}

.status-badge.status-rejected {
    background: #ffebee;
    color: #c62828;
}

.text-muted {
    color: #666666;
    font-style: italic;
}

@media (max-width: 768px) {
    .detail-card-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        text-align: left;
    }

    .detail-value {
        justify-content: flex-start;
        text-align: left;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }

    .card-header__actions {
        justify-content: flex-start;
    }

    .card-main {
        padding: 16px;
    }

    .detail-block {
        padding: 12px;
        gap: 10px;
    }

    .detail-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
@endpush