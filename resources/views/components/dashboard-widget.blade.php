{{-- Dashboard Widget Component --}}
@props([
    'title' => 'Widget',
    'icon' => 'fas fa-chart-bar',
    'type' => 'default',
    'data' => [],
    'permission' => null,
    'class' => ''
])

@if(!$permission || Auth::user()->hasPermission($permission))
<div class="card dashboard-widget {{ $class }}">
    <div class="card-header">
        <h4 class="card-title">
            <i class="{{ $icon }} me-2"></i>
            {{ $title }}
        </h4>
    </div>
    <div class="card-body">
        @if($type === 'stats')
            <div class="dashboard-widget__stats-grid">
                @foreach($data as $key => $value)
                <div class="dashboard-widget__stat-item">
                    <div class="dashboard-widget__stat-number">{{ $value }}</div>
                    <div class="dashboard-widget__stat-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                </div>
                @endforeach
            </div>
        @elseif($type === 'chart')
            <div class="dashboard-widget__chart-container">
                <canvas id="chart-{{ \Illuminate\Support\Str::slug($title) }}"></canvas>
            </div>
        @elseif($type === 'list')
            <div class="dashboard-widget__list">
                @foreach($data as $item)
                <div class="dashboard-widget__list-item">
                    <div class="dashboard-widget__list-content">
                        <div class="dashboard-widget__list-title">{{ $item['title'] ?? 'Item' }}</div>
                        <div class="dashboard-widget__list-subtitle">{{ $item['subtitle'] ?? '' }}</div>
                    </div>
                    <div class="dashboard-widget__list-action">
                        @if(isset($item['url']))
                        <a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="dashboard-widget__content">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
@endif
