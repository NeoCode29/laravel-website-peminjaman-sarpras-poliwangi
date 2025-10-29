@extends('layouts.app')

@section('title', 'Laporan Peminjaman')
@section('subtitle', 'Pantau pengajuan peminjaman dengan filter fleksibel dan ringkasan metrik')

@section('content')
<section class="detail-page report-page">
    <div class="card user-detail-card report-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-chart-bar report-icon"></i>
                <h2 class="card-title">Laporan Peminjaman</h2>
            </div>
            <div class="card-header__actions">
                @can('report.export')
                <form action="{{ route('reports.export') }}" method="GET" class="inline-form">
                    @foreach($filters as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i>
                        Unduh PDF
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <div class="card-main">
            @php
                $selectedStatusLabel = ($filters['status'] ?? null) ? ($statusOptions[$filters['status']] ?? ucfirst($filters['status'])) : 'Semua Status';
                $selectedUserName = ($filters['user_id'] ?? null) ? optional($users->firstWhere('id', (int) $filters['user_id']))->name : 'Semua Peminjam';
                $selectedSarprasName = 'Semua Sarpras';
                if (($filters['sarpras_type'] ?? null) === 'sarana') {
                    $selectedSarprasName = optional($saranaList->firstWhere('id', (int) ($filters['sarpras_id'] ?? null)))->name ?? 'Semua Sarana';
                } elseif (($filters['sarpras_type'] ?? null) === 'prasarana') {
                    $selectedSarprasName = optional($prasaranaList->firstWhere('id', (int) ($filters['sarpras_id'] ?? null)))->name ?? 'Semua Prasarana';
                }
            @endphp

            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-clipboard-list"></i>
                    <div class="chip-text">
                        <span class="chip-label">Total Pengajuan</span>
                        <span class="chip-value">{{ $summary['total_records'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="chip">
                    <i class="fas fa-users"></i>
                    <div class="chip-text">
                        <span class="chip-label">Total Peserta</span>
                        <span class="chip-value">{{ $summary['total_participants'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="chip">
                    <i class="fas fa-clock"></i>
                    <div class="chip-text">
                        <span class="chip-label">Total Jam Terpakai</span>
                        <span class="chip-value">{{ $summary['total_duration_hours'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="chip">
                    <i class="fas fa-boxes"></i>
                    <div class="chip-text">
                        <span class="chip-label">Total Item Disetujui</span>
                        <span class="chip-value">{{ $summary['total_items_approved'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-card-grid report-grid">
                <div class="form-section report-filters-section">
                    <h3 class="section-title">Filter Laporan</h3>
                    <div class="detail-block">
                        <form method="GET" action="{{ route('reports.index') }}" class="filters-form" id="reportFilters">
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
                                    <label for="status" class="filter-label">Status</label>
                                    <select id="status" name="status" class="filter-select">
                                        <option value="">Semua Status</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="user_id" class="filter-label">Peminjam</label>
                                    <select id="user_id" name="user_id" class="filter-select">
                                        <option value="">Semua Peminjam</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label for="search" class="filter-label">Cari</label>
                                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama acara, peminjam, atau sarpras..." class="search-input">
                                </div>
                                <div class="filter-group">
                                    <label for="sarpras_type" class="filter-label">Tipe Sarpras</label>
                                    <select id="sarpras_type" name="sarpras_type" class="filter-select">
                                        <option value="">Semua</option>
                                        <option value="sarana" {{ ($filters['sarpras_type'] ?? '') === 'sarana' ? 'selected' : '' }}>Sarana</option>
                                        <option value="prasarana" {{ ($filters['sarpras_type'] ?? '') === 'prasarana' ? 'selected' : '' }}>Prasarana</option>
                                    </select>
                                </div>
                                <div class="filter-group" data-sarpras-target="sarana">
                                    <label for="sarpras_id_sarana" class="filter-label">Sarana</label>
                                    <select id="sarpras_id_sarana" class="filter-select sarpras-select">
                                        <option value="">Semua Sarana</option>
                                        @foreach($saranaList as $sarana)
                                            <option value="{{ $sarana->id }}" {{ (($filters['sarpras_type'] ?? '') === 'sarana' && ($filters['sarpras_id'] ?? '') == $sarana->id) ? 'selected' : '' }}>{{ $sarana->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group" data-sarpras-target="prasarana">
                                    <label for="sarpras_id_prasarana" class="filter-label">Prasarana</label>
                                    <select id="sarpras_id_prasarana" class="filter-select sarpras-select">
                                        <option value="">Semua Prasarana</option>
                                        @foreach($prasaranaList as $prasarana)
                                            <option value="{{ $prasarana->id }}" {{ (($filters['sarpras_type'] ?? '') === 'prasarana' && ($filters['sarpras_id'] ?? '') == $prasarana->id) ? 'selected' : '' }}>{{ $prasarana->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="sarpras_id" id="sarpras_id" value="{{ $filters['sarpras_id'] ?? '' }}">

                            <div class="form-actions">
                                <div class="form-actions-left">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i>
                                        Terapkan Filter
                                    </button>
                                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i>
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="form-section report-filter-summary">
                    <h3 class="section-title">Ringkasan Filter</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Periode</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">{{ $selectedStatusLabel }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Peminjam</span>
                            <span class="detail-value">{{ $selectedUserName }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Sarpras</span>
                            <span class="detail-value">{{ $selectedSarprasName }}</span>
                        </div>
                        @if(!empty($filters['search']))
                        <div class="detail-row">
                            <span class="detail-label">Kata Kunci</span>
                            <span class="detail-value">{{ $filters['search'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="form-section report-status-section">
                    <h3 class="section-title">Status Pengajuan</h3>
                    <div class="detail-block status-overview">
                        <div class="status-badges">
                            @foreach($statusOptions as $value => $label)
                                <span class="status-badge status-{{ $value }}">
                                    {{ $label }} ({{ $summary['status_counts'][$value] ?? 0 }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Daftar Peminjaman</h3>
                <div class="detail-block table-block">
                    @if($paginator->count() > 0)
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Acara</th>
                                    <th>Peminjam</th>
                                    <th>Periode</th>
                                    <th>Lokasi / Sarpras</th>
                                    <th>Status</th>
                                    <th>Item</th>
                                    <th>Peserta</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paginator as $peminjaman)
                                <tr>
                                    <td>#{{ $peminjaman->id }}</td>
                                    <td>
                                        <div class="table-cell-content">
                                            <span class="table-cell-primary">{{ $peminjaman->event_name ?? '-' }}</span>
                                            <span class="table-cell-secondary">{{ optional($peminjaman->ukm)->nama }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-cell-content">
                                            <span class="table-cell-primary">{{ optional($peminjaman->user)->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-cell-content">
                                            <span class="table-cell-primary">{{ optional($peminjaman->start_date)->format('d M Y') }} - {{ optional($peminjaman->end_date)->format('d M Y') }}</span>
                                            @if($peminjaman->start_time || $peminjaman->end_time)
                                                <span class="table-cell-secondary">{{ $peminjaman->start_time }} - {{ $peminjaman->end_time }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-cell-content">
                                            @if($peminjaman->prasarana)
                                                <span class="table-cell-primary">{{ $peminjaman->prasarana->name }}</span>
                                            @elseif($peminjaman->lokasi_custom)
                                                <span class="table-cell-primary">{{ $peminjaman->lokasi_custom }}</span>
                                            @else
                                                <span class="table-cell-primary">-</span>
                                            @endif
                                            @if($peminjaman->items->count() > 0)
                                                <span class="table-cell-secondary">
                                                    {{ $peminjaman->items->map(function($item) {
                                                        $name = optional($item->sarana)->name;
                                                        $qty = (int) $item->approved_quantity;
                                                        return $name ? trim($name . ($qty > 0 ? ' (' . $qty . ')' : '')) : null;
                                                    })->filter()->unique()->join(', ') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $peminjaman->status }}">{{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}</span>
                                    </td>
                                    <td>
                                        <span class="table-cell-primary">{{ $peminjaman->items->sum(fn($item) => (int) $item->approved_quantity) }}</span>
                                    </td>
                                    <td>
                                        <span class="table-cell-primary">{{ $peminjaman->jumlah_peserta ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="table-cell-content">
                                            <span class="table-cell-primary">{{ optional($peminjaman->created_at)->format('d M Y') }}</span>
                                            <span class="table-cell-secondary">{{ optional($peminjaman->created_at)->format('H:i') }}</span>
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
                                Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }} pengajuan
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
                            <h3 class="empty-state-title">Tidak ada data untuk periode ini</h3>
                            <p class="empty-state-description">Ubah filter atau periode untuk melihat laporan peminjaman.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if(!empty($analytics))
            <div class="form-section">
                <h3 class="section-title">Top Penggunaan Sarana ({{ $filters['start_date'] ?? '' }} - {{ $filters['end_date'] ?? '' }})</h3>
                <div class="detail-block analytics-block">
                    <div class="analytics-grid">
                        @foreach($analytics as $item)
                        <div class="analytics-card">
                            <div class="analytics-header">
                                <span class="analytics-name">{{ $item['name'] }}</span>
                                <span class="analytics-tag">{{ ucfirst($item['type']) }}</span>
                            </div>
                            <div class="analytics-metric">
                                <span class="analytics-label">Total Jam Terpakai</span>
                                <span class="analytics-value">{{ $item['used_hours'] }}</span>
                            </div>
                            <div class="analytics-metric">
                                <span class="analytics-label">Total Item</span>
                                <span class="analytics-value">{{ $item['total_qty'] }}</span>
                            </div>
                            <div class="analytics-progress">
                                <div class="analytics-progress-bar">
                                    <div class="analytics-progress-fill" style="width: {{ $item['utilization_percentage'] ?? 0 }}%"></div>
                                </div>
                                <span class="analytics-progress-text">Utilisasi {{ $item['utilization_percentage'] ?? 0 }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.report-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.report-detail-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.report-detail-card .card-header {
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

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
}

.report-icon {
    font-size: 24px;
    color: #4b5563;
}

.report-detail-card .card-main {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 10px 16px;
    color: #333333;
}

.chip i {
    font-size: 18px;
    color: #6b7280;
}

.chip-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.chip-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.chip-value {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    align-items: stretch;
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
    height: 100%;
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

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    width: 100%;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 13px;
    font-weight: 500;
    color: #333333;
}

.filter-input,
.filter-select,
.search-input {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 14px;
    color: #333333;
    height: 40px;
}

.filter-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
}

.filter-input:focus,
.filter-select:focus,
.search-input:focus {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
}

.form-actions-left {
    display: flex;
    gap: 12px;
}

.status-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    background: #f1f5f9;
    color: #374151;
}

.table-block {
    padding: 0;
}

.table-block .table-wrapper {
    overflow-x: auto;
    border-radius: 8px 8px 0 0;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 960px;
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
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #333333;
}

.table-cell-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.table-cell-primary {
    font-weight: 600;
    color: #111827;
}

.table-cell-secondary {
    font-size: 12px;
    color: #6b7280;
}

.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 8px 8px;
}

.pagination-text {
    font-size: 14px;
    color: #6b7280;
}

.analytics-block {
    padding: 16px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.analytics-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #ffffff;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.analytics-name {
    font-weight: 600;
    color: #111827;
}

.analytics-tag {
    padding: 4px 10px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 600;
}

.analytics-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.analytics-value {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.analytics-progress {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.analytics-progress-bar {
    height: 8px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}

.analytics-progress-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 999px;
}

.analytics-progress-text {
    font-size: 12px;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 48px 20px;
}

.empty-state-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.empty-state-icon {
    font-size: 48px;
    color: #6b7280;
}

.empty-state-title {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.empty-state-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.inline-form {
    display: inline;
}

@media (max-width: 1024px) {
    .report-grid {
        grid-template-columns: 1fr;
    }

    .filters-grid {
        grid-template-columns: repeat(1, minmax(160px, 1fr));
    }
}

@media (max-width: 768px) {
    .report-detail-card .card-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }

    .report-detail-card .card-main {
        padding: 16px;
    }

    .summary-chips {
        gap: 8px;
    }

    .chip {
        width: 100%;
        justify-content: flex-start;
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

    .pagination-section {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('sarpras_type');
    const hiddenSarpras = document.getElementById('sarpras_id');
    const saranaSelect = document.getElementById('sarpras_id_sarana');
    const prasaranaSelect = document.getElementById('sarpras_id_prasarana');
    const filterForm = document.getElementById('reportFilters');

    function syncSarprasInput() {
        const type = typeSelect.value;
        let selectedId = '';

        if (type === 'sarana') {
            selectedId = saranaSelect.value;
        } else if (type === 'prasarana') {
            selectedId = prasaranaSelect.value;
        }

        hiddenSarpras.value = selectedId;
    }

    function toggleSarprasSelect() {
        const type = typeSelect.value;
        document.querySelectorAll('[data-sarpras-target]').forEach(function(group) {
            if (!group) return;
            const target = group.getAttribute('data-sarpras-target');
            group.style.display = !type || type === target ? 'flex' : 'none';
        });

        if (!type) {
            saranaSelect.value = '';
            prasaranaSelect.value = '';
        }

        syncSarprasInput();
    }

    typeSelect.addEventListener('change', function() {
        toggleSarprasSelect();
        syncSarprasInput();
    });

    saranaSelect.addEventListener('change', syncSarprasInput);
    prasaranaSelect.addEventListener('change', syncSarprasInput);

    document.querySelectorAll('#reportFilters input, #reportFilters select').forEach(function(element) {
        element.addEventListener('change', function() {
            if (['start_date', 'end_date', 'status', 'user_id', 'sarpras_type'].includes(element.id)) {
                filterForm.submit();
            }
        });
    });

    toggleSarprasSelect();
});
</script>
@endpush
