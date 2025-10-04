{{-- Stats Card Component --}}
@props([
    'title' => 'Statistik',
    'icon' => 'fas fa-chart-bar',
    'stats' => [],
    'permission' => null,
    'class' => ''
])

@if(!$permission || Auth::user()->hasPermission($permission))
<div class="card stats-card {{ $class }}">
    <div class="card-header">
        <h4 class="card-title">
            <i class="{{ $icon }} me-2"></i>
            {{ $title }}
        </h4>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            @foreach($stats as $key => $value)
            <div class="stat-item">
                <div class="stat-number {{ $this->getStatColor($key) }}">{{ $value }}</div>
                <div class="stat-label">{{ $this->getStatLabel($key) }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.stats-card {
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
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
    margin-bottom: 5px;
}

.stat-number.text-primary { color: #007bff; }
.stat-number.text-success { color: #28a745; }
.stat-number.text-warning { color: #ffc107; }
.stat-number.text-danger { color: #dc3545; }
.stat-number.text-info { color: #17a2b8; }
.stat-number.text-secondary { color: #6c757d; }

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: capitalize;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .stat-item {
        padding: 10px;
    }
    
    .stat-number {
        font-size: 1.25rem;
    }
}
</style>

@php
    public function getStatColor($key)
    {
        $colorMap = [
            'total' => 'text-primary',
            'active' => 'text-success',
            'pending' => 'text-warning',
            'blocked' => 'text-danger',
            'completed' => 'text-info',
            'rejected' => 'text-danger',
            'new_today' => 'text-success',
            'maintenance' => 'text-warning',
            'available' => 'text-success'
        ];
        
        return $colorMap[$key] ?? 'text-primary';
    }
    
    public function getStatLabel($key)
    {
        $labelMap = [
            'total' => 'Total',
            'active' => 'Aktif',
            'pending' => 'Pending',
            'blocked' => 'Diblokir',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'new_today' => 'Baru Hari Ini',
            'maintenance' => 'Maintenance',
            'available' => 'Tersedia',
            'mahasiswa' => 'Mahasiswa',
            'staff' => 'Staff',
            'total_sarana' => 'Total Sarana',
            'total_prasarana' => 'Total Prasarana',
            'available_sarana' => 'Sarana Tersedia',
            'maintenance_sarana' => 'Sarana Maintenance'
        ];
        
        return $labelMap[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
@endphp
@endif
