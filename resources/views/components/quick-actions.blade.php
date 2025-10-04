{{-- Quick Actions Component --}}
@props([
    'actions' => [],
    'columns' => 4
])

@if(count($actions) > 0)
<div class="card quick-actions-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt me-2"></i>
            Aksi Cepat
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($actions as $action)
            <div class="col-md-{{ 12 / $columns }} col-sm-6 mb-3">
                <a href="{{ $action['url'] }}" class="btn btn-{{ $action['color'] ?? 'primary' }} btn-block quick-action-btn">
                    <i class="{{ $action['icon'] ?? 'fas fa-circle' }} me-2"></i>
                    {{ $action['title'] }}
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.quick-actions-card {
    border-left: 4px solid #28a745;
}

.quick-action-btn {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 8px;
    text-decoration: none;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
    color: white;
}

.quick-action-btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

@media (max-width: 768px) {
    .quick-action-btn {
        height: 45px;
        font-size: 0.875rem;
    }
}
</style>
@endif
