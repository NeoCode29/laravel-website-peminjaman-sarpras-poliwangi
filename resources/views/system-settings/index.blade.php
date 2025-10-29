@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('subtitle', 'Konfigurasi global aplikasi sesuai kebijakan pada PRD.')

@section('header-actions')
<div class="header-actions">
    <button type="button" id="refreshBtn" class="btn btn-secondary">
        <i class="fas fa-sync"></i>
        Refresh
    </button>
    <button type="button" id="clearCacheBtn" class="btn btn-secondary">
        <i class="fas fa-trash"></i>
        Hapus Cache
    </button>
</div>
@endsection

@section('content')
<section class="detail-page settings-page">
    <div class="card settings-card">
        <div class="card-main">
            <div id="settingsAlertContainer" aria-live="polite"></div>

            <form id="systemSettingsForm" class="settings-form" novalidate>
                @csrf

                <div class="form-section">
                    <h3 class="section-title">Kebijakan Peminjaman</h3>
                    <div class="detail-block form-grid">
                        <div class="form-group">
                            <label for="max_duration_days" class="form-label required">Durasi Maksimal Peminjaman (Hari)</label>
                            <input
                                type="number"
                                id="max_duration_days"
                                name="max_duration_days"
                                min="1"
                                max="365"
                                value="{{ old('max_duration_days', $settings['max_duration_days'] ?? 7) }}"
                                class="form-input"
                                required>
                            <p class="form-hint">1 - 365 hari</p>
                            <div class="form-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="event_gap_hours" class="form-label required">Jeda Antar Acara (Jam)</label>
                            <input
                                type="number"
                                id="event_gap_hours"
                                name="event_gap_hours"
                                min="0"
                                max="24"
                                value="{{ old('event_gap_hours', $settings['event_gap_hours'] ?? 2) }}"
                                class="form-input"
                                required>
                            <p class="form-hint">0 - 24 jam</p>
                            <div class="form-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="marking_duration_days" class="form-label required">Masa Berlaku Marking (Hari)</label>
                            <input
                                type="number"
                                id="marking_duration_days"
                                name="marking_duration_days"
                                min="1"
                                max="30"
                                value="{{ old('marking_duration_days', $settings['marking_duration_days'] ?? 3) }}"
                                class="form-input"
                                required>
                            <p class="form-hint">1 - 30 hari</p>
                            <div class="form-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="max_planned_submit_days" class="form-label required">Batas Waktu Submit Pengajuan (Hari)</label>
                            <input
                                type="number"
                                id="max_planned_submit_days"
                                name="max_planned_submit_days"
                                min="1"
                                max="365"
                                value="{{ old('max_planned_submit_days', $settings['max_planned_submit_days'] ?? 30) }}"
                                class="form-input"
                                required>
                            <p class="form-hint">1 - 365 hari</p>
                            <div class="form-error"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Pengaturan Tambahan</h3>
                    <div class="detail-block form-grid">
                        <div class="form-group">
                            <label for="max_active_borrowings" class="form-label required">Batas Kuota Peminjaman Aktif per User</label>
                            <input
                                type="number"
                                id="max_active_borrowings"
                                name="max_active_borrowings"
                                min="1"
                                max="10"
                                value="{{ old('max_active_borrowings', $settings['max_active_borrowings'] ?? 3) }}"
                                class="form-input"
                                required>
                            <p class="form-hint">1 - 10</p>
                            <div class="form-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="notifications_enabled" class="form-label required">Status Notifikasi</label>
                            <select
                                id="notifications_enabled"
                                name="notifications_enabled"
                                class="form-select"
                                required>
                                <option value="true" {{ old('notifications_enabled', $settings['notifications_enabled'] ?? 'true') == 'true' ? 'selected' : '' }}>Aktif</option>
                                <option value="false" {{ old('notifications_enabled', $settings['notifications_enabled'] ?? 'true') == 'false' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            <p class="form-hint">Aktifkan atau nonaktifkan notifikasi dalam aplikasi.</p>
                            <div class="form-error"></div>
                        </div>
                    </div>
                </div>

                <div class="detail-actions">
                    <button type="button" id="resetAllBtn" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                        Reset ke Default
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<div id="loadingOverlay" class="loading-overlay" hidden>
    <div class="loading-overlay__spinner">
        <i class="fas fa-sync fa-spin"></i>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/system-settings.css') }}?v={{ filemtime(public_path('css/components/system-settings.css')) }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('systemSettingsForm');
    if (!form) {
        return;
    }

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var loadingOverlay = document.getElementById('loadingOverlay');
    var alertContainer = document.getElementById('settingsAlertContainer');
    var refreshBtn = document.getElementById('refreshBtn');
    var clearCacheBtn = document.getElementById('clearCacheBtn');
    var resetAllBtn = document.getElementById('resetAllBtn');

    var numberRules = {
        max_duration_days: { min: 1, max: 365 },
        event_gap_hours: { min: 0, max: 24 },
        marking_duration_days: { min: 1, max: 30 },
        max_planned_submit_days: { min: 1, max: 365 },
        max_active_borrowings: { min: 1, max: 10 }
    };

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            window.location.reload();
        });
    }

    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function () {
            if (confirm('Apakah Anda yakin ingin menghapus cache pengaturan?')) {
                clearCache();
            }
        });
    }

    if (resetAllBtn) {
        resetAllBtn.addEventListener('click', function () {
            if (confirm('Apakah Anda yakin ingin mereset semua pengaturan ke nilai default?')) {
                resetAllSettings();
            }
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitSettings();
    });

    var numberFields = form.querySelectorAll('input[type="number"]');
    numberFields.forEach(function (input) {
        input.addEventListener('input', function () {
            validateField(input);
        });
    });

    var selectFields = form.querySelectorAll('select');
    selectFields.forEach(function (select) {
        select.addEventListener('change', function () {
            validateField(select);
        });
    });

    function submitSettings() {
        var fieldsToValidate = Array.prototype.slice.call(form.querySelectorAll('input[type="number"], select'));
        var allValid = fieldsToValidate.every(function (field) {
            return validateField(field);
        });

        if (!allValid) {
            showAlert('error', 'Mohon periksa kembali input yang belum valid.');
            return;
        }

        showLoading();
        clearValidationErrors();

        var payload = {
            settings: {
                max_duration_days: {
                    value: form.elements.max_duration_days.value,
                    description: 'Durasi maksimal peminjaman (hari)'
                },
                event_gap_hours: {
                    value: form.elements.event_gap_hours.value,
                    description: 'Jeda antar acara (jam)'
                },
                marking_duration_days: {
                    value: form.elements.marking_duration_days.value,
                    description: 'Masa berlaku marking (hari)'
                },
                max_planned_submit_days: {
                    value: form.elements.max_planned_submit_days.value,
                    description: 'Batas waktu submit pengajuan (hari)'
                },
                max_active_borrowings: {
                    value: form.elements.max_active_borrowings.value,
                    description: 'Batas kuota peminjaman aktif per user'
                },
                notifications_enabled: {
                    value: form.elements.notifications_enabled.value,
                    description: 'Enable/disable notifikasi in-web'
                }
            }
        };

        fetch('{{ route("system-settings.update-multiple") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return {};
                }).then(function (data) {
                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            displayValidationErrors(data.errors);
                            throw new Error('Mohon periksa kembali input yang belum valid.');
                        }

                        throw new Error(data.message || 'Terjadi kesalahan saat menyimpan pengaturan.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                if (data.success) {
                    showAlert('success', data.message || 'Pengaturan berhasil disimpan.');
                } else {
                    showAlert('error', data.message || 'Terjadi kesalahan saat menyimpan pengaturan.');
                }
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Terjadi kesalahan saat menyimpan pengaturan.');
            })
            .finally(function () {
                hideLoading();
            });
    }

    function resetAllSettings() {
        showLoading();
        clearValidationErrors();

        fetch('{{ route("system-settings.reset-all") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return {};
                }).then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan saat mereset pengaturan.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                if (data.success) {
                    showAlert('success', data.message || 'Pengaturan berhasil dikembalikan ke nilai default.');
                    window.location.reload();
                } else {
                    showAlert('error', data.message || 'Terjadi kesalahan saat mereset pengaturan.');
                }
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Terjadi kesalahan saat mereset pengaturan.');
            })
            .finally(function () {
                hideLoading();
            });
    }

    function clearCache() {
        showLoading();

        fetch('{{ route("system-settings.clear-cache") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return {};
                }).then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan saat menghapus cache.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                if (data.success) {
                    showAlert('success', data.message || 'Cache pengaturan berhasil dihapus.');
                } else {
                    showAlert('error', data.message || 'Terjadi kesalahan saat menghapus cache.');
                }
            })
            .catch(function (error) {
                showAlert('error', error.message || 'Terjadi kesalahan saat menghapus cache.');
            })
            .finally(function () {
                hideLoading();
            });
    }

    function validateField(field) {
        var isValid = true;
        var message = '';
        var value = field.value.trim();

        if (field.required && value === '') {
            isValid = false;
            message = 'Field ini wajib diisi.';
        }

        var rule = numberRules[field.name];
        if (isValid && rule) {
            var numericValue = Number(field.value);
            if (Number.isNaN(numericValue) || numericValue < rule.min || numericValue > rule.max) {
                isValid = false;
                message = 'Nilai harus antara ' + rule.min + ' dan ' + rule.max + '.';
            }
        }

        updateFieldState(field, isValid, message);
        return isValid;
    }

    function updateFieldState(field, isValid, message) {
        var formGroup = field.closest('.form-group');
        if (!formGroup) {
            return;
        }

        var errorElement = formGroup.querySelector('.form-error');

        if (isValid) {
            formGroup.classList.remove('has-error');
            if (errorElement) {
                errorElement.textContent = '';
            }
        } else {
            formGroup.classList.add('has-error');
            if (errorElement) {
                errorElement.textContent = message;
            }
        }
    }

    function clearValidationErrors() {
        var errorGroups = form.querySelectorAll('.form-group.has-error');
        errorGroups.forEach(function (group) {
            group.classList.remove('has-error');
            var errorElement = group.querySelector('.form-error');
            if (errorElement) {
                errorElement.textContent = '';
            }
        });
    }

    function displayValidationErrors(errors) {
        clearValidationErrors();

        Object.keys(errors).forEach(function (name) {
            var field = form.elements[name];
            if (field) {
                updateFieldState(field, false, errors[name][0]);
            }
        });
    }

    function showAlert(type, message) {
        if (!alertContainer) {
            return;
        }

        alertContainer.innerHTML = '';

        var alertElement = document.createElement('div');
        alertElement.className = 'settings-alert settings-alert--' + type;
        alertElement.setAttribute('role', 'alert');

        var iconClass = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        var title = type === 'success' ? 'Berhasil' : 'Terjadi Kesalahan';

        alertElement.innerHTML = '' +
            '<i class="fas ' + iconClass + ' settings-alert__icon"></i>' +
            '<div class="settings-alert__content">' +
                '<strong>' + title + '</strong>' +
                '<p>' + message + '</p>' +
            '</div>' +
            '<button type="button" class="settings-alert__close" aria-label="Tutup pemberitahuan">' +
                '<i class="fas fa-times"></i>' +
            '</button>';

        var closeButton = alertElement.querySelector('.settings-alert__close');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                alertElement.remove();
            });
        }

        alertContainer.appendChild(alertElement);

        setTimeout(function () {
            if (alertElement.parentNode) {
                alertElement.remove();
            }
        }, 5000);
    }

    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.removeAttribute('hidden');
        }
    }

    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.setAttribute('hidden', '');
        }
    }
});
</script>
@endpush
