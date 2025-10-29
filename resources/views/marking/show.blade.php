@extends('layouts.app')

@section('title', 'Detail Marking #' . $marking->id)
@section('subtitle', 'Ringkasan lengkap reservasi cepat sarpras')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/marking.css') }}?v={{ filemtime(public_path('css/marking.css')) }}">
@endpush

@php
    $statusBadges = [
        'active' => ['label' => 'Aktif', 'class' => 'status-active'],
        'expired' => ['label' => 'Expired', 'class' => 'status-expired'],
        'converted' => ['label' => 'Dikonversi', 'class' => 'status-converted'],
    ];

    $statusInfo = $statusBadges[$marking->status] ?? ['label' => ucfirst($marking->status), 'class' => 'status-default'];
@endphp

@section('header-actions')
<a href="{{ route('marking.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
@if($marking->user_id === auth()->id() && $marking->status === 'active')
<a href="{{ route('marking.edit', $marking->id) }}" class="btn btn-primary">
    <i class="fas fa-edit"></i>
    Edit Marking
</a>
@endif
@endsection

@section('content')
<section class="detail-page marking-detail-page">
    <div class="card user-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-bookmark user-detail-icon"></i>
                <h2 class="card-title">{{ $marking->event_name }}</h2>
                <span class="status-badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
            </div>
            <div class="card-header__actions">
                <span class="chip chip--outline">
                    <i class="fas fa-hashtag"></i>
                    Marking #{{ $marking->id }}
                </span>
                <span class="chip chip--outline">
                    <i class="fas fa-user"></i>
                    {{ $marking->user->name }}
                </span>
                @if($marking->ukm)
                <span class="chip chip--outline">
                    <i class="fas fa-users"></i>
                    {{ $marking->ukm->nama }}
                </span>
                @endif
            </div>
        </div>

        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-building"></i>
                    {{ $marking->prasarana->name ?? ($marking->lokasi_custom ?? '-') }}
                </div>
                <div class="chip">
                    <i class="fas fa-clock"></i>
                    {{ \Carbon\Carbon::parse($marking->start_datetime)->format('H:i') }} - {{ \Carbon\Carbon::parse($marking->end_datetime)->format('H:i') }}
                </div>
                <div class="chip">
                    <i class="fas fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($marking->start_datetime)->format('d/m/Y') }}
                </div>
                <div class="chip">
                    <i class="fas fa-users"></i>
                    {{ $marking->jumlah_peserta }} Peserta
                </div>
                @if($marking->expires_at)
                <div class="chip chip--outline">
                    <i class="fas fa-hourglass-end"></i>
                    Exp {{ \Carbon\Carbon::parse($marking->expires_at)->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>

            @if($marking->status === 'expired')
            <div class="detail-alert detail-alert--warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Marking Expired</strong>
                    <p>Marking ini sudah melewati batas waktu dan tidak dapat digunakan.</p>
                </div>
            </div>
            @elseif($marking->status === 'converted')
            <div class="detail-alert detail-alert--success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Marking Telah Dikonversi</strong>
                    <p>Marking ini telah berubah menjadi pengajuan peminjaman penuh.</p>
                </div>
            </div>
            @elseif($marking->expires_at && \Carbon\Carbon::parse($marking->expires_at)->isBefore(\Carbon\Carbon::now()->addDay()))
            <div class="detail-alert detail-alert--info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Mendekati Kedaluwarsa</strong>
                    <p>Marking akan kedaluwarsa {{ \Carbon\Carbon::parse($marking->expires_at)->diffForHumans() }}.</p>
                </div>
            </div>
            @endif

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Ringkas</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Acara</span>
                            <span class="detail-value">{{ $marking->event_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Peserta</span>
                            <span class="detail-value">
                                <span class="badge badge--info">{{ $marking->jumlah_peserta }} Orang</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">UKM Penyelenggara</span>
                            <span class="detail-value">
                                @if($marking->ukm)
                                    <span class="role-badge">{{ $marking->ukm->nama }}</span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="status-badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </span>
                        </div>
                        @if($marking->notes)
                        <div class="detail-row">
                            <span class="detail-label">Catatan</span>
                            <span class="detail-value">{{ $marking->notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Jadwal & Durasi</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Mulai</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($marking->start_datetime)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Selesai</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($marking->end_datetime)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Durasi</span>
                            <span class="detail-value">
                                <span class="chip">
                                    <i class="fas fa-hourglass-half"></i>
                                    {{ \Carbon\Carbon::parse($marking->start_datetime)->diffForHumans(\Carbon\Carbon::parse($marking->end_datetime), true) }}
                                </span>
                            </span>
                        </div>
                        @if($marking->planned_submit_by)
                        <div class="detail-row">
                            <span class="detail-label">Rencana Submit</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($marking->planned_submit_by)->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($marking->expires_at)
                        <div class="detail-row">
                            <span class="detail-label">Akan Kedaluwarsa</span>
                            <span class="detail-value">
                                {{ \Carbon\Carbon::parse($marking->expires_at)->format('d/m/Y H:i') }}
                                <span class="chip chip--outline">{{ \Carbon\Carbon::parse($marking->expires_at)->diffForHumans() }}</span>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Lokasi & Prasarana</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Lokasi</span>
                            <span class="detail-value">{{ $marking->prasarana->lokasi ?? ($marking->lokasi_custom ?? '-') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Prasarana</span>
                            <span class="detail-value">
                                @if($marking->prasarana)
                                    <span class="role-badge">{{ $marking->prasarana->name }}</span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        @if(optional($marking->prasarana)->kapasitas)
                        <div class="detail-row">
                            <span class="detail-label">Kapasitas Prasarana</span>
                            <span class="detail-value">{{ $marking->prasarana->kapasitas }} orang</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Informasi Pembuat</h3>
                    <div class="detail-block">
                        <div class="user-summary">
                            <div class="user-summary__avatar">
                                {{ strtoupper(substr($marking->user->name, 0, 1)) }}
                            </div>
                            <div class="user-summary__info">
                                <span class="user-summary__name">{{ $marking->user->name }}</span>
                                <span class="user-summary__email">{{ $marking->user->email }}</span>
                                <span class="user-summary__phone">{{ $marking->user->phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Perencanaan Sarana</h3>
                    <div class="detail-block">
                        @if($marking->items->count())
                        <div class="unit-chip-list">
                            @foreach($marking->items as $item)
                            <span class="unit-chip">
                                <i class="fas fa-cube"></i>
                                {{ $item->sarana->name }}
                                <span class="unit-chip__note">{{ $item->sarana->kategori->name }}</span>
                            </span>
                            @endforeach
                        </div>
                        @else
                        <p class="empty-state">Belum ada sarana yang direncanakan.</p>
                        @endif
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Informasi Sistem</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Dibuat</span>
                            <span class="detail-value">{{ $marking->created_at?->format('d/m/Y H:i') }}<span class="detail-chip">{{ $marking->created_at?->diffForHumans() }}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diperbarui</span>
                            <span class="detail-value">{{ $marking->updated_at?->format('d/m/Y H:i') }}<span class="detail-chip">{{ $marking->updated_at?->diffForHumans() }}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ID Marking</span>
                            <span class="detail-value">#{{ $marking->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    <a href="{{ route('marking.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Daftar
                    </a>

                    @if($marking->user_id === auth()->id() && $marking->status === 'active')
                    <a href="{{ route('marking.edit', $marking->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Marking
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteMarking({{ $marking->id }})">
                        <i class="fas fa-trash"></i>
                        Hapus Marking
                    </button>
                    @endif

                    @can('peminjaman.marking_override')
                    @if($marking->user_id !== auth()->id())
                    <button type="button" class="btn btn-warning" onclick="overrideMarking({{ $marking->id }})">
                        <i class="fas fa-user-times"></i>
                        Override Marking
                    </button>
                    @endif
                    @endcan

                    @if($marking->status === 'active')
                    <button type="button" class="btn btn-success" onclick="convertToPeminjaman({{ $marking->id }})">
                        <i class="fas fa-exchange-alt"></i>
                        Konversi ke Pengajuan
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@include('marking.partials.modals')
@endsection

@push('scripts')
<script src="{{ asset('js/marking.js') }}?v={{ filemtime(public_path('js/marking.js')) }}"></script>
@endpush
