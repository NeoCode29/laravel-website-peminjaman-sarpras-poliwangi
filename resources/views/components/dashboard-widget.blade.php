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
            <div class="stats-grid">
                @foreach($data as $key => $value)
                <div class="stat-item">
                    <div class="stat-number">{{ $value }}</div>
                    <div class="stat-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                </div>
                @endforeach
            </div>
        @elseif($type === 'chart')
            <div class="chart-container">
                <canvas id="chart-{{ Str::slug($title) }}"></canvas>
            </div>
        @elseif($type === 'list')
            <div class="list-container">
                @foreach($data as $item)
                <div class="list-item">
                    <div class="list-content">
                        <div class="list-title">{{ $item['title'] ?? 'Item' }}</div>
                        <div class="list-subtitle">{{ $item['subtitle'] ?? '' }}</div>
                    </div>
                    <div class="list-action">
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
            <div class="widget-content">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>

<style>
.dashboard-widget {
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.stat-item:hover {
    background: #e9ecef;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.chart-container {
    position: relative;
    height: 200px;
}

.list-container {
    max-height: 300px;
    overflow-y: auto;
}

.list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.list-item:last-child {
    border-bottom: none;
}

.list-content {
    flex: 1;
}

.list-title {
    font-weight: 500;
    color: #495057;
    margin-bottom: 2px;
}

.list-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

.list-action {
    margin-left: 10px;
}

.widget-content {
    padding: 10px 0;
}
</style>
@endunless
