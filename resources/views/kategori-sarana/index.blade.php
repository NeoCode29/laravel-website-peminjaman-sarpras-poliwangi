@extends('user-management.layout')

@section('title', 'Manajemen Kategori Sarana')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Manajemen Kategori Sarana</h3>
                @if(Auth::user()->hasPermission('sarpras.create'))
                <a href="{{ route('kategori-sarana.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </a>
                @endif
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('kategori-sarana.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">Cari:</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Nama atau deskripsi kategori...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Kategori Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Icon</th>
                                <th>Jumlah Sarana</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kategori as $item)
                            <tr>
                                <td>{{ $loop->iteration + ($kategori->currentPage() - 1) * $kategori->perPage() }}</td>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                </td>
                                <td>
                                    @if($item->description)
                                        {{ Str::limit($item->description, 50) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->icon)
                                        <i class="{{ $item->icon }} fa-lg"></i>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $item->sarana_count }}</span>
                                </td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(Auth::user()->hasPermission('sarpras.view'))
                                        <a href="{{ route('kategori-sarana.show', $item->id) }}" 
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        
                                        @if(Auth::user()->hasPermission('sarpras.edit'))
                                        <a href="{{ route('kategori-sarana.edit', $item->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        
                                        @if(Auth::user()->hasPermission('sarpras.delete'))
                                        <form action="{{ route('kategori-sarana.destroy', $item->id) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
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
                                <td colspan="7" class="text-center">Tidak ada data kategori sarana.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $kategori->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
