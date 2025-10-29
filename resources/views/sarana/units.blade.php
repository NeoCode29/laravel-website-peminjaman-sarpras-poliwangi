@extends('layouts.app')

@section('title', 'Kelola Unit - ' . $sarana->name)
@section('subtitle', 'Manajemen unit serialized untuk sarana ini')

@section('header-actions')
<a href="{{ route('sarana.show', $sarana->id) }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i>
    Kembali ke Detail
</a>
@can('sarpras.unit_manage')
<button type="button" class="btn btn-primary" onclick="openAddUnitModal()">
    <i class="fas fa-plus"></i>
    Tambah Unit
</button>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/sarana.css') }}?v={{ filemtime(public_path('css/sarana.css')) }}">
<style>
.detail-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.user-detail-card {
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 24px;
    border-bottom: 1px solid #f0f0f0;
}

.card-header__title {
    display: flex;
    align-items: center;
    gap: 14px;
}

.card-header__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.card-subtitle {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: flex-end;
}

.user-detail-icon {
    font-size: 24px;
    color: #4b5563;
}

.summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 24px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    color: #1f2937;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
}

.chip i {
    color: #6b7280;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.section-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.detail-block {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.units-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.units-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.units-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 640px;
}

.units-table th,
.units-table td {
    padding: 12px;
    border-bottom: 1px solid #eef2f7;
    text-align: left;
    font-size: 14px;
    color: #111827;
}

.units-table th {
    background: #f5f7fa;
    font-weight: 600;
}

.empty-units {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    text-align: center;
    color: #4b5563;
}

.empty-units-icon {
    font-size: 40px;
    color: #9ca3af;
}

.empty-units-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.empty-units-description {
    margin: 0;
    max-width: 520px;
    color: #6b7280;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .card-header__title {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .card-title {
        font-size: 20px;
    }

    .summary-chips {
        flex-direction: column;
        align-items: stretch;
    }

    .chip {
        width: 100%;
        justify-content: flex-start;
    }

    .units-header {
        flex-direction: column;
        align-items: stretch;
    }

    .units-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .units-table-wrapper {
        overflow-x: auto;
    }
}
</style>
@endpush

@section('content')
<section class="detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-cubes"></i>
                    {{ $sarana->units()->count() }} Unit Terdaftar
                </div>
                <div class="chip">
                    <i class="fas fa-layer-group"></i>
                    {{ ucfirst($sarana->type) }} Type
                </div>
                <div class="chip">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $sarana->lokasi ?: 'Lokasi belum diisi' }}
                </div>
                <div class="chip">
                    <i class="fas fa-clock"></i>
                    Diperbarui {{ $sarana->updated_at->diffForHumans() }}
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Ringkasan Unit</h3>
                <div class="detail-block">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->units()->count() }}</div>
                                <div class="stat-label">Total Unit</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-success">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->units()->where('unit_status', 'tersedia')->count() }}</div>
                                <div class="stat-label">Tersedia</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-warning">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->units()->where('unit_status', 'rusak')->count() }}</div>
                                <div class="stat-label">Rusak</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-info">
                            <div class="stat-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->units()->where('unit_status', 'maintenance')->count() }}</div>
                                <div class="stat-label">Maintenance</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-danger">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->units()->where('unit_status', 'hilang')->count() }}</div>
                                <div class="stat-label">Hilang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Daftar Unit</h3>
                <div class="detail-block">
                    <div class="units-header">
                        <div class="units-actions">
                            <div class="units-actions__group units-actions__group--search">
                                <div class="search-input-wrapper">
                                    <input type="text" 
                                           id="unitSearch" 
                                           placeholder="Cari unit code..." 
                                           class="search-input">
                                    <i class="fas fa-search search-icon"></i>
                                </div>
                            </div>
                            <div class="units-actions__group units-actions__group--filter">
                                <select id="statusFilter" class="filters-select">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia">Tersedia</option>
                                    <option value="rusak">Rusak</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="hilang">Hilang</option>
                                </select>
                            </div>
                        </div>

                    @if($sarana->units()->count() > 0)
                    <div class="units-table-wrapper">
                        <table class="units-table" id="unitsTable">
                            <thead>
                                <tr>
                                    <th>Unit Code</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Diupdate</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sarana->units()->orderBy('unit_code')->get() as $unit)
                                <tr data-unit-code="{{ strtolower($unit->unit_code) }}" data-status="{{ $unit->unit_status }}">
                                    <td class="unit-code">{{ $unit->unit_code }}</td>
                                    <td>
                                        @php
                                            $isBorrowed = $unit->peminjamanItemUnits()
                                                ->whereHas('peminjaman', function($query) {
                                                    $query->whereIn('status', ['approved', 'picked_up']);
                                                })
                                                ->exists();
                                        @endphp
                                        @if($isBorrowed)
                                            <span class="badge badge-warning">Dipinjam</span>
                                        @else
                                            @switch($unit->unit_status)
                                                @case('tersedia')
                                                    <span class="badge badge-status_active">Tersedia</span>
                                                    @break
                                                @case('rusak')
                                                    <span class="badge badge-status_blocked">Rusak</span>
                                                    @break
                                                @case('maintenance')
                                                    <span class="badge badge-overtime">Maintenance</span>
                                                    @break
                                                @case('hilang')
                                                    <span class="badge badge-status_blocked">Hilang</span>
                                                    @break
                                            @endswitch
                                        @endif
                                    </td>
                                    <td>{{ $unit->created_at->format('d M Y') }}</td>
                                    <td>{{ $unit->updated_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('sarpras.unit_manage')
                                            <button type="button" 
                                                    class="action-button action-button-edit" 
                                                    onclick="editUnit({{ $unit->id }}, '{{ $unit->unit_code }}', '{{ $unit->unit_status }}')"
                                                    title="Edit Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="action-button action-button-delete" 
                                                    onclick="confirmDeleteUnit({{ $unit->id }}, '{{ $unit->unit_code }}')"
                                                    title="Hapus Unit">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-units">
                        <i class="fas fa-box-open empty-units-icon"></i>
                        <h4 class="empty-units-title">Belum Ada Unit</h4>
                        <p class="empty-units-description">
                            Sarana ini belum memiliki unit yang terdaftar. 
                            @can('sarpras.unit_manage')
                                Klik "Tambah Unit" untuk menambahkan unit pertama.
                            @endcan
                        </p>
                        @can('sarpras.unit_manage')
                        <button type="button" class="btn btn-primary" onclick="openAddUnitModal()">
                            <i class="fas fa-plus"></i>
                            Tambah Unit Pertama
                        </button>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Unit Modal -->
<div id="addUnitModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Tambah Unit Baru</h3>
        </div>
        <div class="dialog-body">
            <form id="addUnitForm" action="{{ route('sarana.units.store', $sarana->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="unit_code" class="form-label">Unit Code <span class="text-danger">*</span></label>
                    <input type="text" 
                           id="unit_code" 
                           name="unit_code" 
                           class="form-input"
                           placeholder="Masukkan kode unit (contoh: UNIT-001)"
                           required>
                    <small class="form-help">Kode unit harus unik untuk sarana ini</small>
                </div>
                <div class="form-group">
                    <label for="unit_status" class="form-label">Status Awal <span class="text-danger">*</span></label>
                    <select id="unit_status" name="unit_status" class="form-input" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="rusak">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="hilang">Hilang</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAddUnitModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="saveUnit()">Simpan</button>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div id="editUnitModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Edit Status Unit</h3>
        </div>
        <div class="dialog-body">
            <form id="editUnitForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_unit_id" name="unit_id">
                <div class="form-group">
                    <label for="edit_unit_code" class="form-label">Unit Code <span class="text-danger">*</span></label>
                    <input type="text" 
                           id="edit_unit_code" 
                           name="unit_code" 
                           class="form-input"
                           placeholder="Masukkan kode unit"
                           required>
                    <small class="form-help">Kode unit harus unik untuk sarana ini</small>
                </div>
                <div class="form-group">
                    <label for="edit_unit_status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="edit_unit_status" name="unit_status" class="form-input" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="rusak">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="hilang">Hilang</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditUnitModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="updateUnit()">Update</button>
        </div>
    </div>
</div>

<!-- Delete Unit Modal -->
<div id="deleteUnitModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Hapus Unit</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin menghapus unit <strong id="deleteUnitCode"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteUnitModal()">Batal</button>
            <form id="deleteUnitForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/sarana.js') }}"></script>
<script>
// Alert component functions
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.content-header');
    if (!alertContainer) return;
    
    const alertTypes = {
        'success': { class: 'alert-success', icon: 'fas fa-check-circle', title: 'Berhasil!' },
        'error': { class: 'alert-danger', icon: 'fas fa-exclamation-circle', title: 'Error!' },
        'warning': { class: 'alert-warning', icon: 'fas fa-exclamation-triangle', title: 'Peringatan!' },
        'info': { class: 'alert-info', icon: 'fas fa-info-circle', title: 'Info!' }
    };
    
    const alertConfig = alertTypes[type] || alertTypes['info'];
    
    const alertElement = document.createElement('div');
    alertElement.className = `alert ${alertConfig.class} fade-in`;
    alertElement.setAttribute('role', 'alert');
    alertElement.innerHTML = `
        <i class="${alertConfig.icon} alert-icon"></i>
        <div>
            <strong>${alertConfig.title}</strong> ${message}
        </div>
    `;
    
    // Insert after content header
    alertContainer.insertAdjacentElement('afterend', alertElement);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertElement.parentElement) {
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alertElement.parentElement) {
                    alertElement.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Search and filter functionality
document.getElementById('unitSearch').addEventListener('input', function() {
    filterUnits();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterUnits();
});

function filterUnits() {
    const searchTerm = document.getElementById('unitSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#unitsTable tbody tr');
    
    rows.forEach(row => {
        const unitCode = row.dataset.unitCode;
        const status = row.dataset.status;
        
        const matchesSearch = unitCode.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add Unit Modal
function openAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'flex';
    document.getElementById('unit_code').focus();
}

function closeAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'none';
    document.getElementById('addUnitForm').reset();
}

function saveUnit() {
    const form = document.getElementById('addUnitForm');
    const formData = new FormData(form);
    
    // Validate form
    const unitCode = document.getElementById('unit_code').value.trim();
    if (!unitCode) {
        showAlert('Masukkan kode unit terlebih dahulu!', 'warning');
        return;
    }
    
    // Show loading state
    const saveButton = document.querySelector('#addUnitModal .btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    saveButton.disabled = true;
    
    fetch('{{ route("sarana.units.store", $sarana->id) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Unit ' + unitCode + ' berhasil ditambahkan!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = data.message || 'Validasi gagal:';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += '\n• ' + data.errors[field].join(', ');
                });
                showAlert(errorMessage, 'error');
            } else {
                showAlert(data.message || 'Terjadi kesalahan saat menyimpan unit', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat menyimpan unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// Edit Unit Modal
function editUnit(id, unitCode, status) {
    document.getElementById('edit_unit_id').value = id;
    document.getElementById('edit_unit_code').value = unitCode;
    document.getElementById('edit_unit_status').value = status;
    document.getElementById('editUnitModal').style.display = 'flex';
}

function closeEditUnitModal() {
    document.getElementById('editUnitModal').style.display = 'none';
}

function updateUnit() {
    const form = document.getElementById('editUnitForm');
    const formData = new FormData(form);
    const unitId = document.getElementById('edit_unit_id').value;
    const unitCode = document.getElementById('edit_unit_code').value.trim();
    const unitStatus = document.getElementById('edit_unit_status').value;
    
    // Validate form
    if (!unitId) {
        showAlert('ID unit tidak valid!', 'warning');
        return;
    }
    
    if (!unitCode) {
        showAlert('Masukkan kode unit terlebih dahulu!', 'warning');
        return;
    }
    
    if (!unitStatus) {
        showAlert('Pilih status unit terlebih dahulu!', 'warning');
        return;
    }
    
    // Show loading state
    const updateButton = document.querySelector('#editUnitModal .btn-primary');
    const originalText = updateButton.innerHTML;
    updateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
    updateButton.disabled = true;
    
    fetch(`{{ route("sarana.units.update", [$sarana->id, ":unit"]) }}`.replace(':unit', unitId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const unitCode = document.getElementById('edit_unit_code')?.value || 'Unit';
            showAlert('Unit ' + unitCode + ' berhasil diperbarui!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = data.message || 'Validasi gagal:';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += '\n• ' + data.errors[field].join(', ');
                });
                showAlert(errorMessage, 'error');
            } else {
                showAlert(data.message || 'Terjadi kesalahan saat mengupdate unit', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat mengupdate unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        updateButton.innerHTML = originalText;
        updateButton.disabled = false;
    });
}

// Delete Unit Modal
function confirmDeleteUnit(id, unitCode) {
    document.getElementById('deleteUnitCode').textContent = unitCode;
    document.getElementById('deleteUnitForm').action = `{{ route("sarana.units.destroy", [$sarana->id, ":unit"]) }}`.replace(':unit', id);
    document.getElementById('deleteUnitModal').style.display = 'flex';
}

function closeDeleteUnitModal() {
    document.getElementById('deleteUnitModal').style.display = 'none';
}

// Close modals when clicking backdrop
document.querySelectorAll('.dialog-backdrop').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dialog-backdrop').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
</script>

<style>
.unit-stats-section {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.stat-card.stat-success {
    background: #e8f5e8;
    border-color: #cfead6;
}

.stat-card.stat-warning {
    background: #fff7e6;
    border-color: #ffe7b5;
}

.stat-card.stat-info {
    background: #e7f1ff;
    border-color: #cfe3ff;
}

.stat-card.stat-danger {
    background: #ffe9ec;
    border-color: #ffccd2;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #666666;
}

.stat-success .stat-icon {
    background: #e8f5e8;
    color: #2e7d32;
}

.stat-warning .stat-icon {
    background: #fff7e6;
    color: #f57c00;
}

.stat-info .stat-icon {
    background: #e7f1ff;
    color: #1976d2;
}

.stat-danger .stat-icon {
    background: #ffe9ec;
    color: #c62828;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #333333;
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.units-section {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    padding: 20px;
}

.units-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    gap: 16px;
}

.units-title {
    font-size: 16px;
    font-weight: 500;
    color: #333333;
    margin: 0;
}

.units-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.units-table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.units-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.units-table th {
    background: #f5f7fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333333;
    border-bottom: 1px solid #e0e0e0;
}

.units-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    color: #333333;
}

.unit-code {
    font-family: monospace;
    font-weight: 500;
}

.empty-units {
    text-align: center;
    padding: 40px 20px;
    color: #666666;
}

.empty-units-icon {
    font-size: 48px;
    color: #cccccc;
    margin-bottom: 16px;
}

.empty-units-title {
    font-size: 18px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 8px;
}

.empty-units-description {
    font-size: 14px;
    color: #666666;
    max-width: 400px;
    margin: 0 auto 16px auto;
}

.text-danger {
    color: #dc3545;
}

/* Badge Warning untuk Status Dipinjam */
.badge-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Badge Warning untuk Status Dipinjam */
.badge-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action Buttons - Sesuai Style Guide */
.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #333333;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    font-size: 14px;
}

.action-button:hover {
    background: #f5f5f5;
    border-color: #cccccc;
    transform: translateY(-1px);
}

/* Action Button Variants */
.action-button-view:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-edit:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-reset:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-delete {
    background: #fff5f5;
    border-color: #fed7d7;
    color: #e53e3e;
}

.action-button-delete:hover {
    background: #fed7d7;
    border-color: #feb2b2;
    color: #c53030;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
}

/* Mobile Responsive untuk Action Buttons */
@media (max-width: 768px) {
    .action-buttons {
        gap: 4px;
    }
    
    .action-button {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

.form-help {
    font-size: 12px;
    color: #666666;
    margin-top: 4px;
    display: block;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .units-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .units-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
@endpush