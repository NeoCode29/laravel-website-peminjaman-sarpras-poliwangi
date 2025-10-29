@extends('layouts.dashboard-modular')

@section('title', 'Approval Workflow')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Approval Workflow</h1>
            <p class="text-muted">Detail workflow approval untuk pengajuan: <strong>{{ $peminjaman->event_name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('peminjaman.show', $peminjaman) }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </a>
        </div>
    </div>

    <!-- Peminjaman Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Pengajuan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nama Acara:</strong></td>
                            <td>{{ $peminjaman->event_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Peminjam:</strong></td>
                            <td>{{ $peminjaman->user->name }} ({{ $peminjaman->user->username }})</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal:</strong></td>
                            <td>{{ $peminjaman->start_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu:</strong></td>
                            <td>
                                @if($peminjaman->start_time && $peminjaman->end_time)
                                    {{ $peminjaman->start_time->format('H:i') }} - {{ $peminjaman->end_time->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge {{ $peminjaman->approvalStatus?->overall_status_badge_class ?? 'badge-warning' }}">
                                    {{ $peminjaman->approvalStatus?->overall_status_label ?? 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Global Status:</strong></td>
                            <td>
                                <span class="badge {{ $peminjaman->approvalStatus?->global_status_badge_class ?? 'badge-warning' }}">
                                    {{ $peminjaman->approvalStatus?->global_status_label ?? 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $peminjaman->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $peminjaman->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Prasarana Info -->
    @if($peminjaman->prasarana)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Prasarana</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building fa-2x text-success me-3"></i>
                    <div>
                        <h6 class="mb-1">{{ $peminjaman->prasarana->name }}</h6>
                        <p class="text-muted mb-0">{{ $peminjaman->prasarana->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sarana Info -->
    @if($peminjaman->items->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Sarana</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama Sarana</th>
                                <th>Qty Diminta</th>
                                <th>Qty Disetujui</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peminjaman->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-cube text-primary me-2"></i>
                                            {{ $item->sarana->name }}
                                        </div>
                                    </td>
                                    <td>{{ $item->qty_requested }}</td>
                                    <td>{{ $item->qty_approved ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">Pending</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Approval Workflow -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Workflow Approval</h5>
        </div>
        <div class="card-body">
            @if($peminjaman->approvalWorkflow->count() > 0)
                <!-- Global Approvals -->
                @if($peminjaman->approvalWorkflow->where('approval_type', 'global')->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-globe"></i> Global Approvals
                        </h6>
                        <div class="row">
                            @foreach($peminjaman->approvalWorkflow->where('approval_type', 'global') as $workflow)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $workflow->approver->name }}</h6>
                                                    <p class="text-muted mb-1">{{ $workflow->approver->username }}</p>
                                                    <span class="badge {{ $workflow->level_badge_class }}">
                                                        {{ $workflow->level_label }}
                                                    </span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge {{ $workflow->status_badge_class }}">
                                                        {{ $workflow->status_label }}
                                                    </span>
                                                    @if($workflow->approved_at)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $workflow->approved_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    @elseif($workflow->rejected_at)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $workflow->rejected_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($workflow->notes)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Catatan:</strong> {{ $workflow->notes }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Specific Sarana Approvals -->
                @if($peminjaman->approvalWorkflow->where('approval_type', 'sarana')->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-cube"></i> Sarana Approvals
                        </h6>
                        @foreach($peminjaman->approvalWorkflow->where('approval_type', 'sarana')->groupBy('sarana_id') as $saranaId => $workflows)
                            <div class="mb-3">
                                <h6 class="text-muted">{{ $workflows->first()->sarana->name }}</h6>
                                <div class="row">
                                    @foreach($workflows as $workflow)
                                        <div class="col-md-6 mb-2">
                                            <div class="card border-left-info">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">{{ $workflow->approver->name }}</h6>
                                                            <p class="text-muted mb-1">{{ $workflow->approver->username }}</p>
                                                            <span class="badge {{ $workflow->level_badge_class }}">
                                                                {{ $workflow->level_label }}
                                                            </span>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge {{ $workflow->status_badge_class }}">
                                                                {{ $workflow->status_label }}
                                                            </span>
                                                            @if($workflow->approved_at)
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $workflow->approved_at->format('d/m/Y H:i') }}
                                                                </small>
                                                            @elseif($workflow->rejected_at)
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $workflow->rejected_at->format('d/m/Y H:i') }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($workflow->notes)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>Catatan:</strong> {{ $workflow->notes }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Specific Prasarana Approvals -->
                @if($peminjaman->approvalWorkflow->where('approval_type', 'prasarana')->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-building"></i> Prasarana Approvals
                        </h6>
                        @foreach($peminjaman->approvalWorkflow->where('approval_type', 'prasarana')->groupBy('prasarana_id') as $prasaranaId => $workflows)
                            <div class="mb-3">
                                <h6 class="text-muted">{{ $workflows->first()->prasarana->name }}</h6>
                                <div class="row">
                                    @foreach($workflows as $workflow)
                                        <div class="col-md-6 mb-2">
                                            <div class="card border-left-success">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">{{ $workflow->approver->name }}</h6>
                                                            <p class="text-muted mb-1">{{ $workflow->approver->username }}</p>
                                                            <span class="badge {{ $workflow->level_badge_class }}">
                                                                {{ $workflow->level_label }}
                                                            </span>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge {{ $workflow->status_badge_class }}">
                                                                {{ $workflow->status_label }}
                                                            </span>
                                                            @if($workflow->approved_at)
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $workflow->approved_at->format('d/m/Y H:i') }}
                                                                </small>
                                                            @elseif($workflow->rejected_at)
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $workflow->rejected_at->format('d/m/Y H:i') }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($workflow->notes)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>Catatan:</strong> {{ $workflow->notes }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                    <h5>Belum ada workflow approval</h5>
                    <p class="text-muted">Workflow approval akan dibuat otomatis saat pengajuan dibuat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
</style>
@endpush

