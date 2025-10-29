@extends('layouts.dashboard-modular')

@section('title', 'Detail Approval')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Detail Approval</h1>
            <p class="text-muted">Detail approval workflow untuk pengajuan</p>
        </div>
        <div>
            <a href="{{ route('approvals.pending') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Pending
            </a>
        </div>
    </div>

    <!-- Approval Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Approval</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Tipe Approval:</strong></td>
                            <td>
                                <span class="badge {{ $approval->approval_type == 'global' ? 'badge-primary' : 'badge-info' }}">
                                    {{ $approval->approval_type_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Approver:</strong></td>
                            <td>{{ $approval->approver->name }} ({{ $approval->approver->username }})</td>
                        </tr>
                        <tr>
                            <td><strong>Level:</strong></td>
                            <td>
                                <span class="badge {{ $approval->level_badge_class }}">
                                    {{ $approval->level_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge {{ $approval->status_badge_class }}">
                                    {{ $approval->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($approval->approved_at)
                            <tr>
                                <td><strong>Disetujui:</strong></td>
                                <td>{{ $approval->approved_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($approval->rejected_at)
                            <tr>
                                <td><strong>Ditolak:</strong></td>
                                <td>{{ $approval->rejected_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $approval->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
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
                            <td>{{ $approval->peminjaman->event_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Peminjam:</strong></td>
                            <td>{{ $approval->peminjaman->user->name }} ({{ $approval->peminjaman->user->username }})</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal:</strong></td>
                            <td>{{ $approval->peminjaman->start_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu:</strong></td>
                            <td>
                                @if($approval->peminjaman->start_time && $approval->peminjaman->end_time)
                                    {{ $approval->peminjaman->start_time->format('H:i') }} - {{ $approval->peminjaman->end_time->format('H:i') }}
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
                            <td><strong>Status Pengajuan:</strong></td>
                            <td>
                                <span class="badge {{ $approval->peminjaman->status_badge_class }}">
                                    {{ $approval->peminjaman->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status Approval:</strong></td>
                            <td>
                                <span class="badge {{ $approval->peminjaman->approvalStatus?->overall_status_badge_class ?? 'badge-warning' }}">
                                    {{ $approval->peminjaman->approvalStatus?->overall_status_label ?? 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $approval->peminjaman->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $approval->peminjaman->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sarana/Prasarana Info -->
    @if($approval->sarana)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Sarana</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cube fa-2x text-primary me-3"></i>
                    <div>
                        <h6 class="mb-1">{{ $approval->sarana->name }}</h6>
                        <p class="text-muted mb-0">{{ $approval->sarana->description }}</p>
                        <small class="text-muted">
                            <strong>Kategori:</strong> {{ $approval->sarana->kategori->name ?? '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @elseif($approval->prasarana)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Prasarana</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building fa-2x text-success me-3"></i>
                    <div>
                        <h6 class="mb-1">{{ $approval->prasarana->name }}</h6>
                        <p class="text-muted mb-0">{{ $approval->prasarana->description }}</p>
                        <small class="text-muted">
                            <strong>Kategori:</strong> {{ $approval->prasarana->kategori->name ?? '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Notes -->
    @if($approval->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Catatan</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $approval->notes }}</p>
            </div>
        </div>
    @endif

    <!-- Actions -->
    @if($approval->isPending())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="button" 
                            class="btn btn-success" 
                            onclick="approveWorkflow({{ $approval->id }})">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="rejectWorkflow({{ $approval->id }})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Pengajuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" id="approve_notes" class="form-control" rows="3" 
                                  placeholder="Masukkan catatan approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Pengajuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reject_notes" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="notes" id="reject_notes" class="form-control" rows="3" 
                                  placeholder="Masukkan alasan penolakan..." required></textarea>
                        <div class="form-text">Alasan penolakan wajib diisi.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveWorkflow(workflowId) {
    const form = document.getElementById('approveForm');
    form.action = `/approvals/${workflowId}/approve`;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectWorkflow(workflowId) {
    const form = document.getElementById('rejectForm');
    form.action = `/approvals/${workflowId}/reject`;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

// Auto-submit forms
document.getElementById('approveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal approve pengajuan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat approve pengajuan');
    });
});

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal reject pengajuan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat reject pengajuan');
    });
});
</script>
@endpush

