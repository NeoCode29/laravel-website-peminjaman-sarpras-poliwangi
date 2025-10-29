@extends('layouts.app')

@section('title', 'Assign Approval Prasarana')

@section('content')
<div class="approval-container">
    <div class="approval-header">
        <div class="approval-title">
            <h1>
                <i class="fas fa-building"></i>
                Assign Approval Prasarana
            </h1>
            <p>Kelola approver untuk prasarana yang memerlukan persetujuan</p>
        </div>
        <div class="approval-actions">
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i>
                Tambah Approver
            </button>
            <button class="btn btn-success" onclick="openBulkModal()">
                <i class="fas fa-layer-group"></i>
                Bulk Assign
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="close" onclick="closeAlert(this)">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="close" onclick="closeAlert(this)">&times;</button>
        </div>
    @endif

    @if(request('prasarana_id'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Menampilkan approvers untuk prasarana: <strong>{{ $prasaranas->where('id', request('prasarana_id'))->first()->name ?? 'Tidak ditemukan' }}</strong>
            <a href="{{ route('approval-assignment.prasarana.index') }}" class="btn btn-sm">
                <i class="fas fa-times"></i> Hapus Filter
            </a>
        </div>
    @endif

    <div class="approval-content">
        <div class="table-container">
            <table class="approval-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Prasarana</th>
                        <th>Approver</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prasaranaApprovers as $index => $approver)
                        <tr>
                            <td>{{ $prasaranaApprovers->firstItem() + $index }}</td>
                            <td>
                                <div class="prasarana-info">
                                    <div class="prasarana-image">
                                        @if($approver->prasarana->images->first())
                                            <img src="{{ Storage::url($approver->prasarana->images->first()->image_url) }}" alt="{{ $approver->prasarana->name }}">
                                        @else
                                            <div class="prasarana-placeholder">
                                                <i class="fas fa-building"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="prasarana-details">
                                        <strong>{{ $approver->prasarana->name }}</strong>
                                        <small>{{ $approver->prasarana->kategori->name ?? 'Tidak ada kategori' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="approver-info">
                                    <strong>{{ $approver->approver->name }}</strong>
                                    <small>{{ $approver->approver->email }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-level-{{ $approver->approval_level }}">
                                    {{ $approver->level_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $approver->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $approver->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $approver->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="openEditModal({{ $approver->id }}, {{ $approver->approval_level }}, {{ $approver->is_active ? 'true' : 'false' }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteApprover({{ $approver->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Belum ada approver prasarana yang ditetapkan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-info">
            <p>Menampilkan {{ $prasaranaApprovers->firstItem() }} sampai {{ $prasaranaApprovers->lastItem() }} dari {{ $prasaranaApprovers->total() }} data</p>
            <div class="pagination">
                {{ $prasaranaApprovers->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Approver -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-plus"></i>
                Tambah Prasarana Approver
            </h3>
            <button type="button" class="close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('approval-assignment.prasarana.store') }}" method="POST" onsubmit="return validateForm(this)">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="prasarana_id">Prasarana <span class="required">*</span></label>
                    <select name="prasarana_id" id="prasarana_id" required>
                        <option value="">Pilih Prasarana</option>
                        @foreach($prasaranas as $prasarana)
                            <option value="{{ $prasarana->id }}" {{ (old('prasarana_id') == $prasarana->id || request('prasarana_id') == $prasarana->id) ? 'selected' : '' }}>
                                {{ $prasarana->name }} - {{ $prasarana->kategori->name ?? 'Tidak ada kategori' }}
                            </option>
                        @endforeach
                    </select>
                    @error('prasarana_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="approver_id">Approver <span class="required">*</span></label>
                    <select name="approver_id" id="approver_id" required>
                        <option value="">Pilih Approver</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('approver_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('approver_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="approval_level">Level Approval <span class="required">*</span></label>
                    <select name="approval_level" id="approval_level" required>
                        <option value="">Pilih Level</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('approval_level') == $i ? 'selected' : '' }}>
                                Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                            </option>
                        @endfor
                    </select>
                    @error('approval_level')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Approver -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-edit"></i>
                Edit Prasarana Approver
            </h3>
            <button type="button" class="close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST" onsubmit="return validateForm(this)">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_approval_level">Level Approval <span class="required">*</span></label>
                    <select name="approval_level" id="edit_approval_level" required>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">
                                Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <span class="checkmark"></span>
                        Aktif
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Bulk Assign -->
<div id="bulkModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-layer-group"></i>
                Bulk Assign Prasarana Approvers
            </h3>
            <button type="button" class="close" onclick="closeModal('bulkModal')">&times;</button>
        </div>
        <form action="{{ route('approval-assignment.prasarana.bulk-assign') }}" method="POST" onsubmit="return validateBulkForm(this)">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="bulk_prasarana_ids">Pilih Prasarana <span class="required">*</span></label>
                    <select name="prasarana_ids[]" id="bulk_prasarana_ids" multiple required>
                        @foreach($prasaranas as $prasarana)
                            <option value="{{ $prasarana->id }}">
                                {{ $prasarana->name }} - {{ $prasarana->kategori->name ?? 'Tidak ada kategori' }}
                            </option>
                        @endforeach
                    </select>
                    <small>Gunakan Ctrl+Click untuk memilih multiple prasarana</small>
                </div>

                <div class="form-group">
                    <label for="bulk_approver_id">Approver <span class="required">*</span></label>
                    <select name="approver_id" id="bulk_approver_id" required>
                        <option value="">Pilih Approver</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="bulk_approval_level">Level Approval <span class="required">*</span></label>
                    <select name="approval_level" id="bulk_approval_level" required>
                        <option value="">Pilih Level</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">
                                Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bulkModal')">Batal</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-layer-group"></i>
                    Bulk Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete Hidden -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
/* Reset dan Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.approval-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header Styles */
.approval-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.approval-title h1 {
    color: #2c3e50;
    font-size: 28px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.approval-title p {
    color: #6c757d;
    font-size: 16px;
}

.approval-actions {
    display: flex;
    gap: 12px;
}

/* Button Styles */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
    transform: translateY(-1px);
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* Alert Styles */
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert .close {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: inherit;
}

/* Table Styles */
.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.approval-table {
    width: 100%;
    border-collapse: collapse;
}

.approval-table thead {
    background: #f8f9fa;
}

.approval-table th {
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.approval-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.approval-table tbody tr:hover {
    background: #f8f9fa;
}

/* Prasarana Info Styles */
.prasarana-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.prasarana-image {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.prasarana-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.prasarana-placeholder {
    width: 100%;
    height: 100%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.prasarana-details strong {
    display: block;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 2px;
}

.prasarana-details small {
    color: #6c757d;
    font-size: 12px;
}

.approver-info strong {
    display: block;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 2px;
}

.approver-info small {
    color: #6c757d;
    font-size: 12px;
}

/* Badge Styles */
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.badge-level-1 { background: #007bff; color: white; }
.badge-level-2 { background: #6c757d; color: white; }
.badge-level-3 { background: #17a2b8; color: white; }
.badge-success { background: #28a745; color: white; }
.badge-secondary { background: #6c757d; color: white; }

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 6px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 16px;
}

/* Pagination */
.pagination-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 15px 0;
}

.pagination-info p {
    color: #6c757d;
    font-size: 14px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s ease;
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideIn 0.3s ease;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    color: #2c3e50;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #2c3e50;
}

.required {
    color: #dc3545;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-group select:focus,
.form-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-group small {
    display: block;
    margin-top: 4px;
    color: #6c757d;
    font-size: 12px;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
}

/* Checkbox Styles */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(-20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .approval-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .approval-actions {
        width: 100%;
        justify-content: stretch;
    }
    
    .approval-actions .btn {
        flex: 1;
        justify-content: center;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .approval-table {
        min-width: 600px;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Modal Functions
function openAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function openEditModal(id, level, isActive) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    
    form.action = `/approval-assignment/prasarana/${id}`;
    document.getElementById('edit_approval_level').value = level;
    document.getElementById('edit_is_active').checked = isActive === 'true';
    
    modal.classList.add('show');
}

function openBulkModal() {
    document.getElementById('bulkModal').classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
}

// Alert Functions
function closeAlert(element) {
    element.parentElement.remove();
}

// Form Validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '#ced4da';
        }
    });
    
    if (!isValid) {
        alert('Mohon lengkapi semua field yang wajib diisi');
    }
    
    return isValid;
}

function validateBulkForm(form) {
    const prasaranaIds = form.querySelectorAll('#bulk_prasarana_ids option:checked');
    const approverId = form.querySelector('#bulk_approver_id').value;
    const level = form.querySelector('#bulk_approval_level').value;
    
    if (prasaranaIds.length === 0) {
        alert('Pilih minimal satu prasarana');
        return false;
    }
    
    if (!approverId) {
        alert('Pilih approver');
        return false;
    }
    
    if (!level) {
        alert('Pilih level approval');
        return false;
    }
    
    return true;
}

// Delete Function
function deleteApprover(id) {
    if (confirm('Apakah Anda yakin ingin menghapus approver ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/approval-assignment/prasarana/${id}`;
        form.submit();
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});
</script>
@endpush