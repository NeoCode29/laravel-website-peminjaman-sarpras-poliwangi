@extends('user-management.layout')

@section('title', 'Manajemen Sarana')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Manajemen Sarana</h3>
                @if(Auth::user()->hasPermission('sarpras.create'))
                <a href="{{ route('sarana.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Sarana
                </a>
                @endif
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('sarana.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Cari:</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Nama, deskripsi, atau lokasi...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="kategori_id">Kategori:</label>
                                <select class="form-control" id="kategori_id" name="kategori_id">
                                    <option value="">Semua Kategori</option>
                                    @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type">Tipe:</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">Semua Tipe</option>
                                    <option value="serialized" {{ request('type') == 'serialized' ? 'selected' : '' }}>Serialized</option>
                                    <option value="pooled" {{ request('type') == 'pooled' ? 'selected' : '' }}>Pooled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="kosong" {{ request('status') == 'kosong' ? 'selected' : '' }}>Kosong</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('sarana.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Sarana Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Tipe</th>
                                <th>Total</th>
                                <th>Tersedia</th>
                                <th>Status</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sarana as $item)
                            <tr>
                                <td>{{ $loop->iteration + ($sarana->currentPage() - 1) * $sarana->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->image_url)
                                        <img src="{{ asset('storage/' . $item->image_url) }}" 
                                             alt="{{ $item->name }}" 
                                             class="rounded me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <strong>{{ $item->name }}</strong>
                                            @if($item->description)
                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $item->kategori->name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->type == 'serialized' ? 'primary' : 'success' }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </td>
                                <td>{{ $item->jumlah_total }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->jumlah_tersedia > 0 ? 'success' : 'danger' }}">
                                        {{ $item->jumlah_tersedia }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->jumlah_tersedia > 0)
                                        <span class="badge badge-success">Tersedia</span>
                                    @else
                                        <span class="badge badge-danger">Kosong</span>
                                    @endif
                                </td>
                                <td>{{ $item->lokasi ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(Auth::user()->hasPermission('sarpras.view'))
                                        <a href="{{ route('sarana.show', $item->id) }}" 
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        
                                        @if(Auth::user()->hasPermission('sarpras.edit'))
                                        <a href="{{ route('sarana.edit', $item->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        
                                        @if($item->type == 'serialized' && Auth::user()->hasPermission('sarpras.unit_manage'))
                                        <a href="{{ route('sarana.units', $item->id) }}" 
                                           class="btn btn-sm btn-secondary" title="Kelola Unit">
                                            <i class="fas fa-cogs"></i>
                                        </a>
                                        @endif
                                        
                                        @if(Auth::user()->hasPermission('sarpras.delete'))
                                        <form action="{{ route('sarana.destroy', $item->id) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus sarana ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data sarana.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $sarana->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
