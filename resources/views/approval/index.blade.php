@extends('layouts.dashboard-modular')

@section('title', 'Approval Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Approval Management</h1>
            <p class="text-muted">Kelola semua approval workflow dalam sistem</p>
        </div>
        <div>
            <a href="{{ route('approvals.pending') }}" class="btn btn-primary">
                <i class="fas fa-clock"></i> Pending Approvals
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="approval_type" class="form-label">Tipe Approval</label>
                    <select name="approval_type" id="approval_type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="global" {{ request('approval_type') == 'global' ? 'selected' : '' }}>Global</option>
                        <option value="sarana" {{ request('approval_type') == 'sarana' ? 'selected' : '' }}>Sarana</option>
                        <option value="prasarana" {{ request('approval_type') == 'prasarana' ? 'selected' : '' }}>Prasarana</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="approver_id" class="form-label">Approver</label>
                    <select name="approver_id" id="approver_id" class="form-select">
                        <option value="">Semua Approver</option>
                        @foreach(\App\Models\User::whereHas('roles', function($q) {
                            $q->whereIn('name', ['admin', 'approver']);
                        })->get() as $user)
                            <option value="{{ $user->id }}" {{ request('approver_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Nama acara, sarana, prasarana..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('approval.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approvals List -->
    <div class="card">
        <div class="card-body">
            @if($approvals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Acara</th>
                                <th>Tipe Approval</th>
                                <th>Sarana/Prasarana</th>
                                <th>Peminjam</th>
                                <th>Approver</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvals as $approval)
                                <tr>
                                    <td>{{ $loop->iteration + ($approvals->currentPage() - 1) * $approvals->perPage() }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $approval->peminjaman->event_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $approval->peminjaman->start_date->format('d/m/Y') }} 
                                                {{ $approval->peminjaman->start_time ? $approval->peminjaman->start_time->format('H:i') : '' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $approval->approval_type == 'global' ? 'badge-primary' : 'badge-info' }}">
                                            {{ $approval->approval_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($approval->sarana)
                                            <div>
                                                <i class="fas fa-cube text-primary"></i>
                                                {{ $approval->sarana->name }}
                                            </div>
                                        @elseif($approval->prasarana)
                                            <div>
                                                <i class="fas fa-building text-success"></i>
                                                {{ $approval->prasarana->name }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $approval->peminjaman->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $approval->peminjaman->user->username }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $approval->approver->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $approval->approver->username }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $approval->level_badge_class }}">
                                            {{ $approval->level_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $approval->status_badge_class }}">
                                            {{ $approval->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $approval->created_at->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">{{ $approval->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('approvals.show', $approval) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($approval->isPending())
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="approveWorkflow({{ $approval->id }})"
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="rejectWorkflow({{ $approval->id }})"
                                                        title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $approvals->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h4>Tidak ada approval workflow</h4>
                    <p class="text-muted">Belum ada approval workflow yang sesuai dengan filter yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Workflow</h5>
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
                <h5 class="modal-title">Reject Workflow</h5>
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
    form.action = `/approvals/workflow/${workflowId}/approve`;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectWorkflow(workflowId) {
    const form = document.getElementById('rejectForm');
    form.action = `/approvals/workflow/${workflowId}/reject`;
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
            alert('Error: ' + (data.message || 'Gagal approve workflow'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat approve workflow');
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
            alert('Error: ' + (data.message || 'Gagal reject workflow'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat reject workflow');
    });
});
</script>
@endpush

