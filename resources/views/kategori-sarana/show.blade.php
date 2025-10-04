@extends('user-management.layout')

@section('title', 'Detail Kategori Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Detail Kategori Sarana</h3>
                <div class="btn-group">
                    @if(Auth::user()->hasPermission('sarpras.edit'))
                    <a href="{{ route('kategori-sarana.edit', $kategoriSarana->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        @if($kategoriSarana->icon)
                        <div class="mb-3">
                            <i class="{{ $kategoriSarana->icon }} fa-5x text-primary"></i>
                        </div>
                        @else
                        <div class="mb-3">
                            <i class="fas fa-cube fa-5x text-muted"></i>
                        </div>
                        @endif
                    </div>
                    
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Nama:</strong></td>
                                <td>{{ $kategoriSarana->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Icon:</strong></td>
                                <td>
                                    @if($kategoriSarana->icon)
                                        <i class="{{ $kategoriSarana->icon }} fa-lg"></i>
                                        <code class="ms-2">{{ $kategoriSarana->icon }}</code>
                                    @else
                                        <span class="text-muted">Tidak ada icon</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Sarana:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ $kategoriSarana->sarana->count() }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Dibuat:</strong></td>
                                <td>{{ $kategoriSarana->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Diupdate:</strong></td>
                                <td>{{ $kategoriSarana->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($kategoriSarana->description)
                            <tr>
                                <td><strong>Deskripsi:</strong></td>
                                <td>{{ $kategoriSarana->description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Sarana dalam Kategori -->
        @if($kategoriSarana->sarana->count() > 0)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Daftar Sarana dalam Kategori Ini</h5>
                <span class="badge badge-info">{{ $kategoriSarana->sarana->count() }} sarana</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Sarana</th>
                                <th>Tipe</th>
                                <th>Total</th>
                                <th>Tersedia</th>
                                <th>Lokasi</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoriSarana->sarana as $sarana)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($sarana->image_url)
                                        <img src="{{ asset('storage/' . $sarana->image_url) }}" 
                                             alt="{{ $sarana->name }}" 
                                             class="rounded me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 30px; height: 30px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        @endif
                                        <strong>{{ $sarana->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $sarana->type == 'serialized' ? 'primary' : 'success' }}">
                                        {{ ucfirst($sarana->type) }}
                                    </span>
                                </td>
                                <td>{{ $sarana->jumlah_total }}</td>
                                <td>
                                    <span class="badge badge-{{ $sarana->jumlah_tersedia > 0 ? 'success' : 'danger' }}">
                                        {{ $sarana->jumlah_tersedia }}
                                    </span>
                                </td>
                                <td>{{ $sarana->lokasi ?? '-' }}</td>
                                <td>{{ $sarana->creator->name }}</td>
                                <td>
                                    @if(Auth::user()->hasPermission('sarpras.view'))
                                    <a href="{{ route('sarana.show', $sarana->id) }}" 
                                       class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="card mt-4">
            <div class="card-body text-center">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada sarana dalam kategori ini</h5>
                <p class="text-muted">Kategori ini belum memiliki sarana yang terdaftar.</p>
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
                    <a href="{{ route('kategori-sarana.edit', $kategoriSarana->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Kategori
                    </a>
                    @endif
                    
                    @if(Auth::user()->hasPermission('sarpras.create'))
                    <a href="{{ route('sarana.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Sarana
                    </a>
                    @endif
                    
                    @if(Auth::user()->hasPermission('sarpras.delete'))
                    <form action="{{ route('kategori-sarana.destroy', $kategoriSarana->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Hapus Kategori
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
                <p><strong>ID Kategori:</strong> #{{ $kategoriSarana->id }}</p>
                <p><strong>Nama Unik:</strong> {{ $kategoriSarana->name }}</p>
                
                @if($kategoriSarana->sarana->count() > 0)
                <div class="alert alert-warning">
                    <small>
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Kategori ini memiliki {{ $kategoriSarana->sarana->count() }} sarana. 
                        Hapus semua sarana terlebih dahulu sebelum menghapus kategori.
                    </small>
                </div>
                @else
                <div class="alert alert-success">
                    <small>
                        <i class="fas fa-check-circle"></i>
                        <strong>Status:</strong> Kategori ini dapat dihapus karena belum memiliki sarana.
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
