@extends('layouts.app')

@section('title', 'Notifikasi')
@section('subtitle', 'Kelola dan pantau pemberitahuan terbaru Anda')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}">
@endpush

@section('header-actions')
    @if($notifications->isNotEmpty())
    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="header-actions-group">
        @csrf
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-check-double"></i>
            Tandai semua dibaca
        </button>
    </form>
    @endif
@endsection

@section('content')
<section class="detail-page notifications-page">
    <div class="card notifications-card">
        <div class="card-main">
            @if($notifications->isEmpty())
                <div class="notifications-empty-state">
                    <i class="fas fa-inbox"></i>
                    <div class="notifications-empty-state-title">Belum ada notifikasi</div>
                    <div class="notifications-empty-state-text">Notifikasi baru akan muncul di sini.</div>
                </div>
            @else
                <div class="notifications-list">
                    @foreach($notifications as $notification)
                        <article class="notification-item {{ is_null($notification->read_at) ? 'notification-item--unread' : '' }}">
                            <div class="notification-item__main">
                                <div class="notification-item__icon" aria-hidden="true">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="notification-item__details">
                                    <h3 class="notification-item__title">{{ $notification->title }}</h3>
                                    <p class="notification-item__message">{{ $notification->message }}</p>
                                    <div class="notification-item__meta">
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        @if($notification->expires_at)
                                            <span>• Exp: {{ $notification->expires_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="notification-item__actions">
                                @if(is_null($notification->read_at))
                                    <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-check"></i>
                                            Dibaca
                                        </button>
                                    </form>
                                @endif
                                @if($notification->is_clickable && $notification->action_url)
                                    <a href="{{ route('notifications.click', $notification) }}" class="btn btn-primary btn-sm">
                                        Buka
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                @else
                                    <a href="{{ route('notifications.show', $notification) }}" class="btn btn-secondary btn-sm">
                                        Detail
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination-section :paginator="$notifications->withQueryString()" item-label="notifikasi" />
            @endif
        </div>
    </div>
</section>
@endsection
