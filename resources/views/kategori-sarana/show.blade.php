@extends('layouts.app')

@section('title', 'Detail Kategori Sarana')
@section('subtitle', 'Informasi lengkap kategori sarana')

@section('content')
<section class="detail-page kategori-sarana-detail-page">
    <div class="card kategori-sarana-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-tag kategori-detail-icon"></i>
                <div class="card-header__text">
                    <h2 class="card-title">{{ $kategoriSarana->name }}</h2>
                    <p class="card-subtitle">Informasi kategori sarana</p>
                </div>
                <span class="status-badge {{ ($kategoriSarana->is_active ?? true) ? 'status-approved' : 'status-rejected' }}">
                    <i class="fas {{ ($kategoriSarana->is_active ?? true) ? 'fa-check-circle' : 'fa-ban' }}"></i>
                    {{ ($kategoriSarana->is_active ?? true) ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>
            <div class="card-header__actions">
                <span class="kategori-meta-chip">
                    <i class="fas fa-layer-group"></i>
                    {{ $kategoriSarana->sarana_count ?? 0 }} Sarana
                </span>
            </div>
        </div>

        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i>
                    Dibuat {{ $kategoriSarana->created_at->format('d M Y H:i') }}
                </div>
                <div class="chip">
                    <i class="fas fa-history"></i>
                    Diperbarui {{ $kategoriSarana->updated_at->diffForHumans() }}
                </div>
                <div class="chip">
                    <i class="fas fa-tag"></i>
                    Status {{ ($kategoriSarana->is_active ?? true) ? 'Aktif' : 'Tidak Aktif' }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Kategori</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Kategori</span>
                            <span class="detail-value">{{ $kategoriSarana->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                @if($kategoriSarana->is_active ?? true)
                                    <span class="badge badge-status_active">Aktif</span>
                                @else
                                    <span class="badge badge-status_blocked">Tidak Aktif</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Sarana</span>
                            <span class="detail-value">
                                <span class="badge badge-status_active">{{ $kategoriSarana->sarana_count ?? 0 }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Dibuat</span>
                            <span class="detail-value">{{ $kategoriSarana->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diupdate</span>
                            <span class="detail-value">{{ $kategoriSarana->updated_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="detail-row detail-row--stacked">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-value--multiline">
                                {{ $kategoriSarana->description ?: '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($sarana) && $sarana->count() > 0)
            <div class="form-section">
                <h3 class="section-title">Daftar Sarana</h3>
                <div class="detail-block">
                    <div class="table-section">
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="table-head" style="width: 200px;">Nama Sarana</th>
                                        <th class="table-head" style="width: 100px;">Tipe</th>
                                        <th class="table-head" style="width: 100px;">Total Unit</th>
                                        <th class="table-head" style="width: 100px;">Tersedia</th>
                                        <th class="table-head" style="width: 100px;">Rusak</th>
                                        <th class="table-head" style="width: 120px;">Lokasi</th>
                                        <th class="table-head" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sarana as $item)
                                    <tr>
                                        <td class="table-body">
                                            <div class="user-info">
                                                <div class="user-details">
                                                    <div class="user-name">{{ $item->name }}</div>
                                                    @if($item->description)
                                                        <div class="user-email">{{ Str::limit($item->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="table-body">
                                            @if($item->type == 'serialized')
                                                <span class="badge badge-in_progress">Serialized</span>
                                            @else
                                                <span class="badge badge-done">Pooled</span>
                                            @endif
                                        </td>
                                        <td class="table-body">
                                            <strong>{{ $item->jumlah_total }}</strong>
                                        </td>
                                        <td class="table-body">
                                            <span class="badge badge-status_active">{{ $item->jumlah_tersedia }}</span>
                                        </td>
                                        <td class="table-body">
                                            @if($item->jumlah_rusak > 0)
                                                <span class="badge badge-status_blocked">{{ $item->jumlah_rusak }}</span>
                                            @else
                                                <span class="badge badge-status_active">0</span>
                                            @endif
                                        </td>
                                        <td class="table-body">
                                            {{ $item->lokasi ?? '-' }}
                                        </td>
                                        <td class="table-body">
                                            <div class="action-buttons">
                                                @can('sarpras.view')
                                                <a href="{{ route('sarana.show', $item->id) }}"
                                                   class="action-button action-button-view"
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan

                                                @can('sarpras.edit')
                                                <a href="{{ route('sarana.edit', $item->id) }}"
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

                        @if($sarana->hasPages())
                        <div class="pagination-section">
                            <div class="pagination-info">
                                <span class="pagination-text">
                                    Menampilkan {{ $sarana->firstItem() }}-{{ $sarana->lastItem() }} dari {{ $sarana->total() }} sarana
                                </span>
                            </div>
                            <div class="pagination-controls">
                                <div class="pagination-wrapper">
                                    {{ $sarana->appends(request()->query())->links('pagination.custom') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="form-section">
                <h3 class="section-title">Daftar Sarana</h3>
                <div class="detail-block">
                    <div class="empty-state">
                        <div class="empty-state-container">
                            <i class="fas fa-tools empty-state-icon"></i>
                            <h3 class="empty-state-title">Belum Ada Sarana</h3>
                            <p class="empty-state-description">
                                Belum ada sarana yang menggunakan kategori ini.
                                @can('sarpras.create')
                                    <a href="{{ route('sarana.create') }}">Buat sarana baru</a> untuk menggunakan kategori ini.
                                @endcan
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                    @can('sarpras.edit')
                    <a href="{{ route('kategori-sarana.edit', $kategoriSarana->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Kategori
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kategori-sarana.css') }}?v={{ filemtime(public_path('css/kategori-sarana.css')) }}">
@endpush