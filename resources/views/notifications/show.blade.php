@extends('layouts.app')

@section('title', 'Detail Notifikasi')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('notifications.index') }}">Notifikasi</a>
    <span>/</span>
    <span>Detail</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display:flex; align-items:center; gap: .5rem;">
                <i class="fas fa-bell"></i>
                <span>Detail Notifikasi</span>
            </div>
            <div>
                <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                @if($notification->is_clickable && $notification->action_url)
                    <a href="{{ route('notifications.click', $notification) }}" class="btn btn-primary btn-sm">
                        Buka Tautan
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body" style="padding:20px;">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div style="font-size:20px; font-weight:600; color:#333333; display:flex; gap:.5rem; align-items:center;">
                    <i class="fas fa-bell" style="color:#666666;"></i>
                    <span>{{ $notification->title }}</span>
                </div>
                <div style="color:#666666;">Tipe: <strong>{{ $notification->type }}</strong></div>
                <div style="color:#666666;">Dibuat: <strong>{{ $notification->created_at->format('d M Y H:i') }}</strong></div>
                @if($notification->expires_at)
                    <div style="color:#666666;">Kedaluwarsa: <strong>{{ $notification->expires_at->format('d M Y H:i') }}</strong></div>
                @endif
                <hr style="margin:12px 0;">
                <div style="font-size:14px; color:#333333; white-space:pre-wrap;">{{ $notification->message }}</div>
            </div>
        </div>
    </div>
@endsection


