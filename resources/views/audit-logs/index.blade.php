@extends('layouts.app')

@section('title', 'Log Aktivitas')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}?v={{ filemtime(public_path('css/reports.css')) }}">
@endpush

@section('content')
<div class="page-content">
    <div class="card report-card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="card-title">
                        <i class="fas fa-history card-title-icon"></i>
                        Log Aktivitas
                    </h1>
                    <p class="card-subtitle">Pantau perubahan data dan aktivitas pengguna dalam sistem</p>
                </div>
            </div>
        </div>

        <div class="card-main">
            <div class="filters-section">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="filters-form" id="auditFilters">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="start_date" class="filter-label">Periode Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="filter-input">
                        </div>
                        <div class="filter-group">
                            <label for="end_date" class="filter-label">Periode Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="filter-input">
                        </div>
                        <div class="filter-group">
                            <label for="user_id" class="filter-label">Pengguna</label>
                            <select id="user_id" name="user_id" class="filter-select">
                                <option value="">Semua Pengguna</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="action" class="filter-label">Aksi</label>
                            <select id="action" name="action" class="filter-select">
                                <option value="">Semua Aksi</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="model_type" class="filter-label">Model</label>
                            <select id="model_type" name="model_type" class="filter-select">
                                <option value="">Semua Model</option>
                                @foreach($models as $model)
                                    <option value="{{ $model }}" {{ ($filters['model_type'] ?? '') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="ip_address" class="filter-label">IP Address</label>
                            <input type="text" id="ip_address" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}" placeholder="Contoh: 192.168." class="filter-input">
                        </div>
                        <div class="filter-group">
                            <label for="search" class="filter-label">Cari</label>
                            <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari deskripsi, aksi, model..." class="search-input">
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="form-actions-left">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="summary-section">
                <div class="summary-grid">
                    <div class="summary-card">
                        <span class="summary-label">Total Log</span>
                        <span class="summary-value">{{ $summary['total_records'] ?? 0 }}</span>
                    </div>
                    <div class="summary-card">
                        <span class="summary-label">Pengguna Unik</span>
                        <span class="summary-value">{{ $summary['unique_users'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="table-section audit-log-table">
                @if($paginator->count() > 0)
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Model</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginator as $log)
                            <tr>
                                <td>
                                    <div class="table-cell-content">
                                        <span class="table-cell-primary">{{ optional($log->created_at)->format('d M Y H:i:s') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-cell-content">
                                        <span class="table-cell-primary">{{ optional($log->user)->name ?? 'System' }}</span>
                                        <span class="table-cell-secondary">ID: {{ $log->user_id ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="table-cell-primary">{{ $log->action ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="table-cell-content">
                                        <span class="table-cell-primary">{{ $log->model_type ?? '-' }}</span>
                                        <span class="table-cell-secondary">Model ID: {{ $log->model_id ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="audit-log-detail">
                                        {{ $log->description ?? '-' }}
                                        @if($log->old_values)
                                            <br><strong>Sebelum:</strong> {{ Str::limit($log->old_values, 200) }}
                                        @endif
                                        @if($log->new_values)
                                            <br><strong>Sesudah:</strong> {{ Str::limit($log->new_values, 200) }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="table-cell-primary">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="audit-log-detail">{{ Str::limit($log->user_agent, 150) ?? '-' }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-section">
                    <div class="pagination-info">
                        <span class="pagination-text">
                            Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }} log
                        </span>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-wrapper">
                            {{ $paginator->links() }}
                        </div>
                    </div>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-state-container">
                        <i class="fas fa-inbox empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak ada log untuk filter ini</h3>
                        <p class="empty-state-description">Ubah filter atau periode untuk melihat aktivitas lain.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('auditFilters');
    const autoSubmitFields = ['start_date', 'end_date', 'user_id', 'action', 'model_type'];

    document.querySelectorAll('#auditFilters input, #auditFilters select').forEach(function(element) {
        element.addEventListener('change', function() {
            if (autoSubmitFields.includes(element.id)) {
                filterForm.submit();
            }
        });
    });
});
</script>
@endpush
