{{-- Quick Actions Component --}}
@props([
    'actions' => []
])

@if(count($actions) > 0)
<div class="quick-actions">
    <div class="quick-actions__grid">
        @foreach($actions as $action)
            <a href="{{ $action['url'] }}" class="quick-actions__item" data-color="{{ $action['color'] ?? 'primary' }}">
                <span class="quick-actions__icon">
                    <i class="{{ $action['icon'] ?? 'fas fa-circle' }}"></i>
                </span>
                <span class="quick-actions__label">{{ $action['title'] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
