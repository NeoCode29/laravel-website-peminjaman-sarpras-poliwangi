@extends('user-management.layout')

@section('title', 'Detail Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Detail Sarana</h3>
                <div class="btn-group">
                    @if(Auth::user()->hasPermission('sarpras.edit'))
                    <a href="{{ route('sarana.edit', $sarana->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    
                    @if($sarana->type == 'serialized' && Auth::user()->hasPermission('sarpras.unit_manage'))
                    <a href="{{ route('sarana.units', $sarana->id) }}" class="btn btn-secondary">
                        <i class="fas fa-cogs"></i> Kelola Unit
                    </a>
                    @endif
                    
                    <a href="{{ route('sarana.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($sarana->image_url)
                        <img src="{{ asset('storage/' . $sarana->image_url) }}" 
                             alt="{{ $sarana->name }}" 
                             class="img-fluid rounded mb-3" 
                             style="max-height: 300px; width: 100%; object-fit: cover;">
                        @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 300px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <p>Tidak ada gambar</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Nama:</strong></td>
                                <td>{{ $sarana->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kategori:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ $sarana->kategori->name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tipe:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $sarana->type == 'serialized' ? 'primary' : 'success' }}">
                                        {{ ucfirst($sarana->type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Lokasi:</strong></td>
                                <td>{{ $sarana->lokasi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat oleh:</strong></td>
                                <td>{{ $sarana->creator->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Dibuat:</strong></td>
                                <td>{{ $sarana->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($sarana->description)
                            <tr>
                                <td><strong>Deskripsi:</strong></td>
                                <td>{{ $sarana->description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Sarana -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Statistik Ketersediaan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-primary">{{ $sarana->jumlah_total }}</h3>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-success">{{ $sarana->jumlah_tersedia }}</h3>
                            <p class="text-muted mb-0">Tersedia</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-warning">{{ $sarana->jumlah_rusak }}</h3>
                            <p class="text-muted mb-0">Rusak</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-danger">{{ $sarana->jumlah_hilang }}</h3>
                            <p class="text-muted mb-0">Hilang</p>
                        </div>
                    </div>
                </div>
                
                @if($sarana->type == 'serialized')
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $sarana->jumlah_total > 0 ? ($sarana->jumlah_tersedia / $sarana->jumlah_total) * 100 : 0 }}%">
                        </div>
                    </div>
                    <small class="text-muted">
                        Tingkat ketersediaan: {{ $sarana->jumlah_total > 0 ? number_format(($sarana->jumlah_tersedia / $sarana->jumlah_total) * 100, 1) : 0 }}%
                    </small>
                </div>
                @endif
            </div>
        </div>

        @if($sarana->type == 'serialized' && $sarana->units->count() > 0)
        <!-- Daftar Unit (untuk sarana serialized) -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Daftar Unit</h5>
                <span class="badge badge-info">{{ $sarana->units->count() }} unit</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Unit</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sarana->units as $unit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $unit->unit_code }}</code></td>
                                <td>
                                    @switch($unit->unit_status)
                                        @case('tersedia')
                                            <span class="badge badge-success">Tersedia</span>
                                            @break
                                        @case('rusak')
                                            <span class="badge badge-danger">Rusak</span>
                                            @break
                                        @case('maintenance')
                                            <span class="badge badge-warning">Maintenance</span>
                                            @break
                                        @case('hilang')
                                            <span class="badge badge-dark">Hilang</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $unit->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(Auth::user()->hasPermission('sarpras.edit'))
                    <a href="{{ route('sarana.edit', $sarana->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Sarana
                    </a>
                    @endif
                    
                    @if($sarana->type == 'serialized' && Auth::user()->hasPermission('sarpras.unit_manage'))
                    <a href="{{ route('sarana.units', $sarana->id) }}" class="btn btn-secondary">
                        <i class="fas fa-cogs"></i> Kelola Unit
                    </a>
                    @endif
                    
                    @if(Auth::user()->hasPermission('sarpras.delete'))
                    <form action="{{ route('sarana.destroy', $sarana->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus sarana ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Hapus Sarana
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informasi Tambahan -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Informasi</h5>
            </div>
            <div class="card-body">
                <p><strong>ID Sarana:</strong> #{{ $sarana->id }}</p>
                <p><strong>Terakhir Diupdate:</strong> {{ $sarana->updated_at->format('d/m/Y H:i') }}</p>
                
                @if($sarana->type == 'serialized')
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        <strong>Serialized:</strong> Statistik dihitung otomatis dari unit yang terdaftar.
                    </small>
                </div>
                @else
                <div class="alert alert-success">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        <strong>Pooled:</strong> Statistik dihitung manual berdasarkan stok dan peminjaman aktif.
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
