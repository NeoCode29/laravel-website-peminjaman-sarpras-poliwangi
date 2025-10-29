@extends('layouts.app')

@section('title', 'Edit Marking #' . $marking->id)
@section('subtitle', 'Perbarui informasi reservasi cepat sarpras')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/marking.css') }}?v={{ filemtime(public_path('css/marking.css')) }}">
@endpush

@section('header-actions')
<a href="{{ route('marking.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
<a href="{{ route('marking.show', $marking->id) }}" class="btn btn-info">
    <i class="fas fa-eye"></i>
    Lihat Detail
</a>
@endsection

@section('content')
<section class="detail-page marking-form-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form method="POST" action="{{ route('marking.update', $marking->id) }}" id="markingForm" class="marking-form">
                @csrf
                @method('PUT')

                <div class="detail-card-grid">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Acara</h3>
                        <div class="detail-block form-grid form-grid--two">
                            <div class="form-group form-group--full">
                                <label for="event_name" class="form-label required">Nama Acara</label>
                                <input type="text"
                                       id="event_name"
                                       name="event_name"
                                       value="{{ old('event_name', $marking->event_name) }}"
                                       class="form-input @error('event_name') is-invalid @enderror"
                                       placeholder="Masukkan nama acara"
                                       required>
                                @error('event_name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="jumlah_peserta" class="form-label required">Jumlah Peserta</label>
                                <input type="number"
                                       id="jumlah_peserta"
                                       name="jumlah_peserta"
                                       value="{{ old('jumlah_peserta', $marking->jumlah_peserta) }}"
                                       class="form-input @error('jumlah_peserta') is-invalid @enderror"
                                       placeholder="Masukkan jumlah peserta"
                                       min="1"
                                       required>
                                @error('jumlah_peserta')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="ukm_id" class="form-label">UKM Penyelenggara</label>
                                <select id="ukm_id"
                                        name="ukm_id"
                                        class="form-select @error('ukm_id') is-invalid @enderror">
                                    <option value="">Pilih UKM (Opsional)</option>
                                    @foreach($ukms as $ukm)
                                        <option value="{{ $ukm->id }}" {{ old('ukm_id', $marking->ukm_id) == $ukm->id ? 'selected' : '' }}>
                                            {{ $ukm->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ukm_id')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group form-group--full">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea id="notes"
                                          name="notes"
                                          rows="3"
                                          class="form-input form-textarea @error('notes') is-invalid @enderror"
                                          placeholder="Catatan tambahan untuk marking">{{ old('notes', $marking->notes) }}</textarea>
                                @error('notes')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Lokasi Acara</h3>
                        <div class="detail-block">
                            <div class="form-group">
                                <label class="form-label">Pilih Lokasi</label>
                                <div class="form-radio-group">
                                    <label class="form-radio-item">
                                        <input type="radio"
                                               name="location_type"
                                               value="prasarana"
                                               {{ old('location_type', $marking->prasarana_id ? 'prasarana' : 'custom') == 'prasarana' ? 'checked' : '' }}
                                               onchange="toggleLocationType()">
                                        <span class="form-radio-label">Prasarana Tersedia</span>
                                    </label>
                                    <label class="form-radio-item">
                                        <input type="radio"
                                               name="location_type"
                                               value="custom"
                                               {{ old('location_type', $marking->lokasi_custom ? 'custom' : 'prasarana') == 'custom' ? 'checked' : '' }}
                                               onchange="toggleLocationType()">
                                        <span class="form-radio-label">Lokasi Custom</span>
                                    </label>
                                </div>
                            </div>

                            <div id="prasarana-section" class="location-section">
                                <div class="form-group">
                                    <label for="prasarana_id" class="form-label required">Prasarana</label>
                                    <select id="prasarana_id"
                                            name="prasarana_id"
                                            class="form-select @error('prasarana_id') is-invalid @enderror"
                                            onchange="loadPrasaranaInfo()">
                                        <option value="">Pilih Prasarana</option>
                                        @foreach($prasaranas as $prasarana)
                                            <option value="{{ $prasarana->id }}"
                                                    data-kapasitas="{{ $prasarana->kapasitas }}"
                                                    data-lokasi="{{ $prasarana->lokasi }}"
                                                    {{ old('prasarana_id', $marking->prasarana_id) == $prasarana->id ? 'selected' : '' }}>
                                                {{ $prasarana->name }} (Kapasitas: {{ $prasarana->kapasitas }} orang)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('prasarana_id')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="prasarana-info" class="info-card info-card--inline" style="display: none;">
                                    <p><strong>Kapasitas:</strong> <span id="info-kapasitas">-</span> orang</p>
                                    <p><strong>Lokasi:</strong> <span id="info-lokasi">-</span></p>
                                </div>
                            </div>

                            <div id="custom-section" class="location-section" style="display: none;">
                                <div class="form-group">
                                    <label for="lokasi_custom" class="form-label required">Lokasi Custom</label>
                                    <input type="text"
                                           id="lokasi_custom"
                                           name="lokasi_custom"
                                           value="{{ old('lokasi_custom', $marking->lokasi_custom) }}"
                                           class="form-input @error('lokasi_custom') is-invalid @enderror"
                                           placeholder="Masukkan lokasi custom">
                                    @error('lokasi_custom')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Waktu Pelaksanaan</h3>
                        <div class="detail-block form-grid form-grid--two">
                            <div class="form-group">
                                <label for="start_datetime" class="form-label required">Tanggal & Waktu Mulai</label>
                                <input type="datetime-local"
                                       id="start_datetime"
                                       name="start_datetime"
                                       value="{{ old('start_datetime', \Carbon\Carbon::parse($marking->start_datetime)->format('Y-m-d\TH:i')) }}"
                                       class="form-input @error('start_datetime') is-invalid @enderror"
                                       onchange="calculateEndTime()"
                                       required>
                                @error('start_datetime')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="end_datetime" class="form-label required">Tanggal & Waktu Selesai</label>
                                <input type="datetime-local"
                                       id="end_datetime"
                                       name="end_datetime"
                                       value="{{ old('end_datetime', \Carbon\Carbon::parse($marking->end_datetime)->format('Y-m-d\TH:i')) }}"
                                       class="form-input @error('end_datetime') is-invalid @enderror"
                                       required>
                                @error('end_datetime')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="planned_submit_by" class="form-label">Rencana Submit Pengajuan</label>
                                <input type="datetime-local"
                                       id="planned_submit_by"
                                       name="planned_submit_by"
                                       value="{{ old('planned_submit_by', $marking->planned_submit_by ? \Carbon\Carbon::parse($marking->planned_submit_by)->format('Y-m-d\TH:i') : '') }}"
                                       class="form-input @error('planned_submit_by') is-invalid @enderror">
                                <small class="form-help">Kapan Anda berencana mengajukan peminjaman formal (opsional)</small>
                                @error('planned_submit_by')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Perencanaan Sarana</h3>
                        <div class="detail-block">
                            <div class="form-group">
                                <label for="sarana_search" class="form-label">Cari Sarana</label>
                                <div class="search-input-wrapper">
                                    <input type="text" id="sarana_search" class="search-input" placeholder="Cari sarana..." onkeyup="filterSarana()">
                                    <i class="fas fa-search search-icon"></i>
                                </div>
                            </div>

                            <div class="sarana-list" id="sarana-list">
                                @foreach($saranas as $sarana)
                                <div class="sarana-item" data-name="{{ strtolower($sarana->name) }}">
                                    <label class="sarana-checkbox">
                                        <input type="checkbox" name="sarana_ids[]" value="{{ $sarana->id }}"
                                               {{ in_array($sarana->id, old('sarana_ids', $marking->items->pluck('sarana_id')->toArray())) ? 'checked' : '' }}>
                                        <span class="sarana-name">{{ $sarana->name }}</span>
                                        <span class="sarana-category">{{ $sarana->kategori->name }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            @error('sarana_ids')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('marking.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Marking
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function toggleLocationType() {
    const prasaranaSection = document.getElementById('prasarana-section');
    const customSection = document.getElementById('custom-section');
    const prasaranaRadio = document.querySelector('input[name="location_type"][value="prasarana"]');
    const customRadio = document.querySelector('input[name="location_type"][value="custom"]');
    
    if (prasaranaRadio.checked) {
        prasaranaSection.style.display = 'block';
        customSection.style.display = 'none';
        document.getElementById('prasarana_id').required = true;
        document.getElementById('lokasi_custom').required = false;
    } else if (customRadio.checked) {
        prasaranaSection.style.display = 'none';
        customSection.style.display = 'block';
        document.getElementById('prasarana_id').required = false;
        document.getElementById('lokasi_custom').required = true;
    }
}

function loadPrasaranaInfo() {
    const prasaranaSelect = document.getElementById('prasarana_id');
    const selectedOption = prasaranaSelect.options[prasaranaSelect.selectedIndex];
    const infoSection = document.getElementById('prasarana-info');
    
    if (selectedOption.value) {
        const kapasitas = selectedOption.getAttribute('data-kapasitas');
        const lokasi = selectedOption.getAttribute('data-lokasi');
        
        document.getElementById('info-kapasitas').textContent = kapasitas;
        document.getElementById('info-lokasi').textContent = lokasi;
        infoSection.style.display = 'block';
    } else {
        infoSection.style.display = 'none';
    }
}

function calculateEndTime() {
    const startDateTime = document.getElementById('start_datetime').value;
    if (startDateTime) {
        const startDate = new Date(startDateTime);
        const endDate = new Date(startDate.getTime() + (2 * 60 * 60 * 1000)); // Default 2 hours
        
        const endDateTime = endDate.toISOString().slice(0, 16);
        document.getElementById('end_datetime').value = endDateTime;
    }
}

function filterSarana() {
    const searchTerm = document.getElementById('sarana_search').value.toLowerCase();
    const saranaItems = document.querySelectorAll('.sarana-item');
    
    saranaItems.forEach(item => {
        const saranaName = item.getAttribute('data-name');
        if (saranaName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    toggleLocationType();
    loadPrasaranaInfo();
    
    // Set minimum date to today
    const today = new Date().toISOString().slice(0, 16);
    document.getElementById('start_datetime').min = today;
    document.getElementById('end_datetime').min = today;
    document.getElementById('planned_submit_by').min = today;
});
</script>
@endpush
