@extends('user-management.layout')

@section('title', 'Kelola Unit Sarana')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Kelola Unit - {{ $sarana->name }}</h3>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                        <i class="fas fa-plus"></i> Tambah Unit
                    </button>
                    <a href="{{ route('sarana.show', $sarana->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Detail
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Sarana -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tipe:</strong> {{ ucfirst($sarana->type) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Jumlah Total:</strong> {{ $sarana->jumlah_total }}
                        </div>
                        <div class="col-md-3">
                            <strong>Unit Terdaftar:</strong> {{ $units->count() }}
                        </div>
                        <div class="col-md-3">
                            <strong>Sisa Unit:</strong> {{ $sarana->jumlah_total - $units->count() }}
                        </div>
                    </div>
                </div>

                <!-- Daftar Unit -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Unit</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $unit->unit_code }}</code></td>
                                <td>
                                    <select class="form-select form-select-sm status-select" 
                                            data-unit-id="{{ $unit->id }}" 
                                            data-current-status="{{ $unit->unit_status }}">
                                        <option value="tersedia" {{ $unit->unit_status == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                        <option value="rusak" {{ $unit->unit_status == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                        <option value="maintenance" {{ $unit->unit_status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="hilang" {{ $unit->unit_status == 'hilang' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                </td>
                                <td>{{ $unit->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger delete-unit" 
                                            data-unit-id="{{ $unit->id }}" 
                                            data-unit-code="{{ $unit->unit_code }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada unit yang terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Statistik Unit -->
                @if($units->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ $units->where('unit_status', 'tersedia')->count() }}</h4>
                                <p class="mb-0">Tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h4>{{ $units->where('unit_status', 'rusak')->count() }}</h4>
                                <p class="mb-0">Rusak</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4>{{ $units->where('unit_status', 'maintenance')->count() }}</h4>
                                <p class="mb-0">Maintenance</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h4>{{ $units->where('unit_status', 'hilang')->count() }}</h4>
                                <p class="mb-0">Hilang</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Unit -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUnitForm" action="{{ route('sarana.store-unit', $sarana->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Unit Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unit_code">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('unit_code') is-invalid @enderror" 
                               id="unit_code" name="unit_code" required 
                               placeholder="Contoh: PROJ-001, LAPTOP-001">
                        @error('unit_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Kode unit harus unik untuk sarana ini. Contoh: PROJ-001, LAPTOP-001
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form untuk update status unit -->
<form id="updateUnitForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="unit_status" id="update_unit_status">
</form>

<!-- Form untuk delete unit -->
<form id="deleteUnitForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update status unit
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const unitId = this.dataset.unitId;
            const newStatus = this.value;
            const currentStatus = this.dataset.currentStatus;
            
            if (newStatus === currentStatus) {
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin mengubah status unit menjadi "${newStatus}"?`)) {
                const form = document.getElementById('updateUnitForm');
                form.action = `/sarana/units/${unitId}`;
                document.getElementById('update_unit_status').value = newStatus;
                form.submit();
            } else {
                // Reset to current status
                this.value = currentStatus;
            }
        });
    });
    
    // Delete unit
    document.querySelectorAll('.delete-unit').forEach(button => {
        button.addEventListener('click', function() {
            const unitId = this.dataset.unitId;
            const unitCode = this.dataset.unitCode;
            
            if (confirm(`Apakah Anda yakin ingin menghapus unit "${unitCode}"?`)) {
                const form = document.getElementById('deleteUnitForm');
                form.action = `/sarana/units/${unitId}`;
                form.submit();
            }
        });
    });
    
    // Reset form when modal is closed
    document.getElementById('addUnitModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('addUnitForm').reset();
    });
});
</script>
@endsection
