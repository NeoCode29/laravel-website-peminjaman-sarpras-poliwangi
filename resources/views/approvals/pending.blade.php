@extends('layouts.dashboard-modular')

@section('title', 'Pending Approvals')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Pending Approvals</h1>
            <p class="text-muted">Daftar pengajuan yang memerlukan persetujuan Anda</p>
        </div>
        <div>
            <span class="badge badge-warning badge-lg">{{ $workflows->total() }} Pending</span>
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
                    <label for="search" class="form-label">Cari</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Nama acara, sarana, prasarana..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('approvals.pending') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approvals List -->
    <div class="card">
        <div class="card-body">
            @if($workflows->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Acara</th>
                                <th>Tipe Approval</th>
                                <th>Sarana/Prasarana</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workflows as $workflow)
                                <tr>
                                    <td>{{ $loop->iteration + ($workflows->currentPage() - 1) * $workflows->perPage() }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $workflow->peminjaman->event_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $workflow->peminjaman->start_date->format('d/m/Y') }} 
                                                {{ $workflow->peminjaman->start_time ? $workflow->peminjaman->start_time->format('H:i') : '' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $workflow->approval_type == 'global' ? 'badge-primary' : 'badge-info' }}">
                                            {{ $workflow->approval_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($workflow->sarana)
                                            <div>
                                                <i class="fas fa-cube text-primary"></i>
                                                {{ $workflow->sarana->name }}
                                            </div>
                                        @elseif($workflow->prasarana)
                                            <div>
                                                <i class="fas fa-building text-success"></i>
                                                {{ $workflow->prasarana->name }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $workflow->peminjaman->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $workflow->peminjaman->user->username }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $workflow->created_at->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">{{ $workflow->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $workflow->level_badge_class }}">
                                            {{ $workflow->level_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $workflow->status_badge_class }}">
                                            {{ $workflow->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('peminjaman.show', $workflow->peminjaman) }}" class="btn btn-sm btn-primary">
                                            Kelola di Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $workflows->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>Tidak ada pengajuan pending</h4>
                    <p class="text-muted">Semua pengajuan telah diproses atau tidak ada pengajuan yang memerlukan persetujuan Anda.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
