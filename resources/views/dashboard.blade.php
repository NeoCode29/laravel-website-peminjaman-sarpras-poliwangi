@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang, ' . $user->name . '!')

@section('content')
<section class="detail-page dashboard-detail">
    <div class="card user-detail-card dashboard-detail-card">
        <div class="card-main" id="dashboardRoot"
             data-dashboard-config='@json($dashboardData)'>

            @if(!empty($dashboardData['quick_actions']))
            <div class="form-section">
                <div class="detail-section-header">
                    <h3 class="section-title">Aksi Cepat</h3>
                </div>
                <div class="detail-block detail-block--padded detail-block--flush" data-quick-actions>
                    <x-quick-actions
                        :actions="$dashboardData['quick_actions']"
                    />
                </div>
            </div>
            @endif

            <div class="form-section">
                <div class="detail-section-header">
                    <h3 class="section-title">Kalender Peminjaman</h3>
                </div>
                <div class="detail-block detail-block--padded">
                    <div class="calendar-layout">
                        <div class="calendar-panel">
                            <div class="calendar-header">
                                <button type="button" class="calendar-nav" id="dashboardCalendarPrev" aria-label="Bulan sebelumnya">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="calendar-header__month" id="dashboardCalendarMonth">-</div>
                                <button type="button" class="calendar-nav" id="dashboardCalendarNext" aria-label="Bulan berikutnya">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <div class="calendar-grid" id="dashboardCalendarGrid"></div>
                        </div>

                        <div class="calendar-detail" id="dashboardCalendarDetail">
                            <div class="calendar-detail__header">
                                <h3 class="calendar-detail__header-title">Detail Peminjaman</h3>
                            </div>
                            <div class="calendar-detail__list">
                                <div class="calendar-detail__placeholder">
                                    <i class="fas fa-calendar-day"></i>
                                    <p>Pilih tanggal dengan peminjaman untuk melihat detail.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="detail-section-header detail-section-header--split">
                    <div>
                        <h3 class="section-title">Tren Peminjaman Tahunan</h3>
                    </div>
                    <div class="filter-group">
                        <label for="yearlyLoanSelect">Tahun</label>
                        <select id="yearlyLoanSelect" class="filter-control"></select>
                    </div>
                </div>
                <div class="detail-block detail-block--padded">
                    <div class="loan-trend">
                        <div class="loan-trend__chart" id="yearlyLoanChart">
                            <div class="loan-trend__placeholder">
                                <i class="fas fa-chart-line"></i>
                                <p>Data grafik belum tersedia.</p>
                            </div>
                        </div>
                        <div class="loan-trend__sidebar">
                            <div class="loan-trend__stats">
                                <div class="snapshot-grid snapshot-grid--responsive">
                                    <article class="snapshot-card snapshot-card--stretch" data-stat-card="peminjaman.total">
                                        <div class="snapshot-card__icon snapshot-card__icon--primary">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <div class="snapshot-card__body">
                                            <span class="snapshot-card__label">Total Pengajuan</span>
                                            <span class="snapshot-card__value" data-stat-value>0</span>
                                        </div>
                                    </article>
                                    <article class="snapshot-card snapshot-card--stretch" data-stat-card="peminjaman.pending">
                                        <div class="snapshot-card__icon snapshot-card__icon--warning">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <div class="snapshot-card__body">
                                            <span class="snapshot-card__label">Pending</span>
                                            <span class="snapshot-card__value" data-stat-value>0</span>
                                        </div>
                                    </article>
                                    <article class="snapshot-card snapshot-card--stretch" data-stat-card="peminjaman.active">
                                        <div class="snapshot-card__icon snapshot-card__icon--info">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <div class="snapshot-card__body">
                                            <span class="snapshot-card__label">Aktif</span>
                                            <span class="snapshot-card__value" data-stat-value>0</span>
                                        </div>
                                    </article>
                                    <article class="snapshot-card snapshot-card--stretch" data-stat-card="peminjaman.completed">
                                        <div class="snapshot-card__icon snapshot-card__icon--success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="snapshot-card__body">
                                            <span class="snapshot-card__label">Selesai</span>
                                            <span class="snapshot-card__value" data-stat-value>0</span>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.dashboardManager && typeof window.dashboardManager.loadDashboardData === 'function') {
            window.dashboardManager.loadDashboardData();
        }
    });
</script>
@endpush

@endsection