@extends('layouts.app')

@section('title', 'Edit Peminjaman')
@section('subtitle', 'Perbarui detail pengajuan peminjaman Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/peminjaman.css') }}?v={{ filemtime(public_path('css/peminjaman.css')) }}">
@endpush

@section('content')
@php
    $existingItems = $peminjaman->items->map(function ($item) {
        return [
            'sarana_id' => $item->sarana_id,
            'qty_requested' => $item->qty_requested,
            'notes' => $item->notes,
        ];
    })->toArray();

    $loanTypeDefault = 'sarana';
    if ($peminjaman->prasarana_id && $peminjaman->items->isNotEmpty()) {
        $loanTypeDefault = 'both';
    } elseif ($peminjaman->prasarana_id) {
        $loanTypeDefault = 'prasarana';
    }

    $loanTypeValue = old('loan_type', $loanTypeDefault);
    $oldItems = old('sarana_items', $existingItems);
@endphp
<section class="detail-page edit-peminjaman-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Catatan:</strong>
                    <span>Pengajuan hanya dapat diubah selama status masih pending.</span>
                </div>
            </div>

            <form id="peminjamanEditForm" action="{{ route('peminjaman.update', $peminjaman->id) }}" method="POST" enctype="multipart/form-data" class="user-create-form user-edit-form peminjaman-edit-form">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h3 class="section-title">Detail Acara</h3>

                    <div class="detail-block form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="event_name" class="form-label required">
                                Nama Acara
                            </label>
                            <input type="text"
                                   id="event_name"
                                   name="event_name"
                                   class="form-input @error('event_name') is-invalid @enderror"
                                   value="{{ old('event_name', $peminjaman->event_name) }}"
                                   placeholder="Contoh: Festival Budaya Kampus 2025"
                                   autocomplete="off"
                                   required>
                            @error('event_name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->user()->user_type === 'mahasiswa')
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="ukm_id" class="form-label required">
                                UKM Penyelenggara
                            </label>
                            <select id="ukm_id"
                                    name="ukm_id"
                                    class="form-select @error('ukm_id') is-invalid @enderror"
                                    required>
                                <option value="">Pilih UKM</option>
                                @foreach(($ukms ?? []) as $u)
                                    <option value="{{ $u->id }}" {{ old('ukm_id', $peminjaman->ukm_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ukm_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="start_date" class="form-label required">
                                Tanggal Mulai
                            </label>
                            <input type="date"
                                   id="start_date"
                                   name="start_date"
                                   class="form-input @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', $peminjaman->start_date) }}"
                                   required>
                            @error('start_date')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="form-label required">
                                Tanggal Selesai
                            </label>
                            <input type="date"
                                   id="end_date"
                                   name="end_date"
                                   class="form-input @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', $peminjaman->end_date) }}"
                                   required>
                            @error('end_date')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="form-help">Durasi maksimal: {{ $maxDuration ?? 7 }} hari</small>
                        </div>

                        <div class="form-group">
                            <label for="start_time" class="form-label">
                                Waktu Mulai
                            </label>
                            <input type="time"
                                   id="start_time"
                                   name="start_time"
                                   class="form-input @error('start_time') is-invalid @enderror"
                                   value="{{ old('start_time', $peminjaman->start_time) }}">
                            @error('start_time')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="end_time" class="form-label">
                                Waktu Selesai
                            </label>
                            <input type="time"
                                   id="end_time"
                                   name="end_time"
                                   class="form-input @error('end_time') is-invalid @enderror"
                                   value="{{ old('end_time', $peminjaman->end_time) }}">
                            @error('end_time')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="loan_type" class="form-label required">
                                Jenis Peminjaman
                            </label>
                            <select id="loan_type"
                                    name="loan_type"
                                    class="form-select @error('loan_type') is-invalid @enderror"
                                    required>
                                <option value="">Pilih Jenis Peminjaman</option>
                                <option value="sarana" {{ $loanTypeValue === 'sarana' ? 'selected' : '' }}>Peminjaman Sarana</option>
                                <option value="prasarana" {{ $loanTypeValue === 'prasarana' ? 'selected' : '' }}>Peminjaman Prasarana</option>
                                <option value="both" {{ $loanTypeValue === 'both' ? 'selected' : '' }}>Peminjaman Sarana dan Prasarana</option>
                            </select>
                            @error('loan_type')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="form-help">Pilih cakupan peminjaman sesuai kebutuhan Anda</small>
                        </div>
                    </div>
                </div>

                <div class="form-section" id="section-lokasi" style="display: none;">
                    <h3 class="section-title">Lokasi Acara</h3>

                    <div class="detail-block">
                        <div class="form-group">
                            <label for="lokasi_custom" class="form-label required">
                                Lokasi Acara
                            </label>
                            <input type="text"
                                   id="lokasi_custom"
                                   name="lokasi_custom"
                                   class="form-input @error('lokasi_custom') is-invalid @enderror"
                                   value="{{ old('lokasi_custom', $peminjaman->lokasi_custom) }}"
                                   placeholder="Contoh: Lapangan Utama, Aula Kampus, dll">
                            @error('lokasi_custom')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="form-help">Isi jika Anda tidak memilih prasarana kampus</small>
                        </div>
                    </div>
                </div>

                <div class="form-section" id="section-prasarana" style="display: none;">
                    <h3 class="section-title">Prasarana</h3>

                    <div class="detail-block">
                        <div class="form-group">
                            <label for="prasarana_id" class="form-label required">
                                Pilih Prasarana
                            </label>
                            <select id="prasarana_id"
                                    name="prasarana_id"
                                    class="form-select @error('prasarana_id') is-invalid @enderror">
                                <option value="">Pilih Prasarana</option>
                                @foreach(($prasarana ?? []) as $p)
                                    <option value="{{ $p->id }}"
                                            data-kapasitas="{{ $p->kapasitas ?? '-' }}"
                                            data-keterangan="{{ $p->description ?? '-' }}"
                                            {{ old('prasarana_id', $peminjaman->prasarana_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prasarana_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="prasarana-info-card" id="prasarana-info" style="display: none;">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-users"></i>
                                    <span>Kapasitas</span>
                                </div>
                                <div class="info-value" id="prasarana-kapasitas">-</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Keterangan</span>
                                </div>
                                <div class="info-value" id="prasarana-keterangan">-</div>
                            </div>
                        </div>

                        <div class="form-group" id="jumlah_peserta_group" style="display: none;">
                            <label for="jumlah_peserta" class="form-label required">
                                Jumlah Peserta
                            </label>
                            <input type="number"
                                   id="jumlah_peserta"
                                   name="jumlah_peserta"
                                   class="form-input @error('jumlah_peserta') is-invalid @enderror"
                                   value="{{ old('jumlah_peserta', $peminjaman->jumlah_peserta) }}"
                                   min="1"
                                   placeholder="Perkiraan jumlah peserta">
                            @error('jumlah_peserta')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="form-help">Pastikan jumlah peserta tidak melebihi kapasitas prasarana.</small>
                        </div>
                    </div>
                </div>

                <div class="form-section" id="section-sarana" style="display: none;">
                    <h3 class="section-title">Daftar Sarana</h3>

                    <div class="detail-block">
                        <div id="saranaItems" class="sarana-todo-list">
                            @if(!empty($oldItems))
                                @foreach($oldItems as $idx => $it)
                                <div class="sarana-todo-item" data-index="{{ $idx }}">
                                    <div class="todo-checkbox">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <div class="todo-content">
                                        <div class="todo-select">
                                            <select name="sarana_items[{{ $idx }}][sarana_id]"
                                                    class="form-select sarana-select"
                                                    required>
                                                <option value="">Pilih Sarana</option>
                                                @foreach(($sarana ?? []) as $s)
                                                    <option value="{{ $s->id }}" {{ ($it['sarana_id'] ?? null) == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }} ({{ ucfirst($s->type) }}) - Tersedia: {{ $s->jumlah_tersedia }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="todo-qty">
                                            <label class="form-label-inline">Jumlah:</label>
                                            <input type="number"
                                                   name="sarana_items[{{ $idx }}][qty_requested]"
                                                   class="form-input form-input-qty"
                                                   value="{{ $it['qty_requested'] ?? 1 }}"
                                                   min="1"
                                                   required>
                                        </div>
                                        <div class="todo-notes">
                                            <input type="text"
                                                   name="sarana_items[{{ $idx }}][notes]"
                                                   class="form-input"
                                                   value="{{ $it['notes'] ?? '' }}"
                                                   placeholder="Catatan (opsional)">
                                        </div>
                                    </div>
                                    <button type="button"
                                            class="todo-delete btn-remove-item"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="sarana-empty-state" id="saranaEmptyState" style="{{ !empty($oldItems) ? 'display: none;' : '' }}">
                            <i class="fas fa-tools" style="font-size: 48px; color: #ccc; margin-bottom: 12px;"></i>
                            <p style="color: #666; margin: 0;">Belum ada sarana yang ditambahkan</p>
                            <p style="color: #999; font-size: 12px; margin: 4px 0 0 0;">Klik tombol di bawah untuk menambahkan</p>
                        </div>

                        <div class="sarana-toolbar">
                            <button type="button" class="btn btn-secondary" id="addItemBtn">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Sarana</span>
                            </button>
                        </div>

                        @error('sarana_items')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Surat Pengajuan</h3>

                    <div class="detail-block">
                        @if($peminjaman->surat_path)
                        <div class="current-file-info" style="margin-bottom: 16px;">
                            <div class="file-info">
                                <i class="fas fa-file-alt"></i>
                                <span>File saat ini: {{ basename($peminjaman->surat_path) }}</span>
                                <a href="{{ Storage::url($peminjaman->surat_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </div>
                        </div>
                        @endif

                        <div class="uploader-dnd-area @error('surat') uploader-dnd-error @enderror" id="suratDrop">
                            <input type="file"
                                   id="surat"
                                   name="surat"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="uploader-dnd-input">
                            <div class="uploader-dnd-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Tarik & letakkan file di sini</p>
                                <p>atau klik untuk memilih file</p>
                                <small class="form-help">Format: PDF, JPG, PNG (Maks. 5MB)</small>
                            </div>
                        </div>
                        <div class="uploader-dnd-preview" id="suratPreview"></div>
                        @error('surat')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="detail-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('peminjaman.show', $peminjaman->id) }}'">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/peminjaman.js') }}?v={{ filemtime(public_path('js/peminjaman.js')) }}"></script>
<script>
    window.__PEMINJAMAN_FORM_CONTEXT__ = {
        sarana: @json($sarana ?? []),
        prasarana: @json($prasarana ?? []),
        layout: 'todo',
        selectors: {
            container: '#saranaItems',
            addBtn: '#addItemBtn'
        },
        isEdit: true,
        peminjamanId: {{ $peminjaman->id }}
    };

    document.addEventListener('DOMContentLoaded', function() {
        const loanTypeSelect = document.getElementById('loan_type');
        const sectionLokasi = document.getElementById('section-lokasi');
        const sectionPrasarana = document.getElementById('section-prasarana');
        const sectionSarana = document.getElementById('section-sarana');
        const lokasiInput = document.getElementById('lokasi_custom');
        const prasaranaSelect = document.getElementById('prasarana_id');
        const jumlahPesertaGroup = document.getElementById('jumlah_peserta_group');
        const jumlahPesertaInput = document.getElementById('jumlah_peserta');

        function toggleSections() {
            const loanType = loanTypeSelect ? loanTypeSelect.value : '';

            if (sectionLokasi) sectionLokasi.style.display = 'none';
            if (sectionPrasarana) sectionPrasarana.style.display = 'none';
            if (sectionSarana) sectionSarana.style.display = 'none';

            if (lokasiInput) lokasiInput.removeAttribute('required');
            if (prasaranaSelect) prasaranaSelect.removeAttribute('required');
            if (jumlahPesertaInput) jumlahPesertaInput.removeAttribute('required');

            if (loanType === 'sarana') {
                if (sectionLokasi) sectionLokasi.style.display = 'block';
                if (sectionSarana) sectionSarana.style.display = 'block';
                if (lokasiInput) lokasiInput.setAttribute('required', 'required');
                if (jumlahPesertaGroup) jumlahPesertaGroup.style.display = 'none';
            } else if (loanType === 'prasarana') {
                if (sectionPrasarana) sectionPrasarana.style.display = 'block';
                if (prasaranaSelect) prasaranaSelect.setAttribute('required', 'required');
                if (jumlahPesertaGroup) jumlahPesertaGroup.style.display = 'block';
                if (jumlahPesertaInput) jumlahPesertaInput.setAttribute('required', 'required');
            } else if (loanType === 'both') {
                if (sectionPrasarana) sectionPrasarana.style.display = 'block';
                if (sectionSarana) sectionSarana.style.display = 'block';
                if (prasaranaSelect) prasaranaSelect.setAttribute('required', 'required');
                if (jumlahPesertaGroup) jumlahPesertaGroup.style.display = 'block';
                if (jumlahPesertaInput) jumlahPesertaInput.setAttribute('required', 'required');
            }

            updateEmptyState();
        }

        function updatePrasaranaInfo() {
            if (!prasaranaSelect) return;
            const selectedOption = prasaranaSelect.options[prasaranaSelect.selectedIndex];
            const infoCard = document.getElementById('prasarana-info');

            if (selectedOption && selectedOption.value) {
                const kapasitas = selectedOption.getAttribute('data-kapasitas');
                const keterangan = selectedOption.getAttribute('data-keterangan');

                const kapasitasEl = document.getElementById('prasarana-kapasitas');
                const keteranganEl = document.getElementById('prasarana-keterangan');
                if (kapasitasEl) kapasitasEl.textContent = kapasitas || '-';
                if (keteranganEl) keteranganEl.textContent = keterangan || '-';

                if (infoCard) infoCard.style.display = 'block';
                if (jumlahPesertaGroup) jumlahPesertaGroup.style.display = 'block';
                if (jumlahPesertaInput) jumlahPesertaInput.setAttribute('required', 'required');
            } else {
                if (infoCard) infoCard.style.display = 'none';
                if (jumlahPesertaGroup && loanTypeSelect && loanTypeSelect.value !== 'prasarana' && loanTypeSelect.value !== 'both') {
                    jumlahPesertaGroup.style.display = 'none';
                }
            }
        }

        function updateEmptyState() {
            const container = document.getElementById('saranaItems');
            const emptyState = document.getElementById('saranaEmptyState');
            const hasItems = container ? container.querySelectorAll('.sarana-todo-item').length > 0 : false;
            if (emptyState) {
                emptyState.style.display = hasItems ? 'none' : 'flex';
            }
        }

        if (loanTypeSelect) loanTypeSelect.addEventListener('change', toggleSections);
        if (prasaranaSelect) prasaranaSelect.addEventListener('change', updatePrasaranaInfo);

        toggleSections();
        updatePrasaranaInfo();
        updateEmptyState();

        const addBtn = document.getElementById('addItemBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const container = document.getElementById('saranaItems');
                if (!container) return;
                const index = container.querySelectorAll('.sarana-todo-item').length;
                addSaranaTodoItem(container, index);
                updateEmptyState();
            });
        }

        const saranaContainer = document.getElementById('saranaItems');
        if (saranaContainer) {
            saranaContainer.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-item')) {
                    const item = e.target.closest('.sarana-todo-item');
                    if (item) {
                        item.remove();
                        updateEmptyState();
                    }
                }
            });
        }

        const form = document.getElementById('peminjamanEditForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const prasaranaId = prasaranaSelect ? prasaranaSelect.value : '';
                const lokasiCustom = lokasiInput ? lokasiInput.value.trim() : '';

                if (!prasaranaId && !lokasiCustom) {
                    e.preventDefault();
                    alert('Pilih prasarana atau masukkan lokasi custom');
                    return false;
                }

                if (prasaranaId && lokasiCustom) {
                    e.preventDefault();
                    alert('Pilih salah satu: prasarana atau lokasi custom');
                    return false;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                }
            });
        }

        function addSaranaTodoItem(container, index) {
            const saranaList = window.__PEMINJAMAN_FORM_CONTEXT__.sarana;
            const item = document.createElement('div');
            item.className = 'sarana-todo-item';
            item.setAttribute('data-index', index);
            item.innerHTML = `
                <div class="todo-checkbox">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div class="todo-content">
                    <div class="todo-select">
                        <select name="sarana_items[${index}][sarana_id]" class="form-input sarana-select" required>
                            <option value="">Pilih Sarana</option>
                            ${saranaList.map(s => `<option value="${s.id}">${escapeHtml(s.name)} (${capitalize(s.type)}) - Tersedia: ${s.jumlah_tersedia || 0}</option>`).join('')}
                        </select>
                    </div>
                    <div class="todo-qty">
                        <label class="form-label-inline">Jumlah:</label>
                        <input type="number" name="sarana_items[${index}][qty_requested]" class="form-input form-input-qty" value="1" min="1" required>
                    </div>
                    <div class="todo-notes">
                        <input type="text" name="sarana_items[${index}][notes]" class="form-input" placeholder="Catatan (opsional)">
                    </div>
                </div>
                <button type="button" class="todo-delete btn-remove-item" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(item);
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        function capitalize(str) {
            if (!str) return '';
            return String(str).charAt(0).toUpperCase() + String(str).slice(1);
        }
    });
</script>
@endpush
