// Sarana Management JavaScript

// Alert component functions
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.content-header');
    if (!alertContainer) return;
    
    const alertTypes = {
        'success': { class: 'alert-success', icon: 'fas fa-check-circle', title: 'Berhasil!' },
        'error': { class: 'alert-danger', icon: 'fas fa-exclamation-circle', title: 'Error!' },
        'warning': { class: 'alert-warning', icon: 'fas fa-exclamation-triangle', title: 'Peringatan!' },
        'info': { class: 'alert-info', icon: 'fas fa-info-circle', title: 'Info!' }
    };
    
    const alertConfig = alertTypes[type] || alertTypes['info'];
    
    const alertElement = document.createElement('div');
    alertElement.className = `alert ${alertConfig.class} fade-in`;
    alertElement.setAttribute('role', 'alert');
    alertElement.innerHTML = `
        <i class="${alertConfig.icon} alert-icon"></i>
        <div>
            <strong>${alertConfig.title}</strong> ${message}
        </div>
    `;
    
    // Insert after content header
    alertContainer.insertAdjacentElement('afterend', alertElement);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertElement.parentElement) {
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alertElement.parentElement) {
                    alertElement.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Form validation and interaction functions
function toggleTypeFields() {
    const serializedRadio = document.getElementById('type_serialized');
    const pooledRadio = document.getElementById('type_pooled');
    const jumlahTotalHelp = document.getElementById('jumlah_total_help');
    
    if (serializedRadio && pooledRadio && jumlahTotalHelp) {
        if (serializedRadio.checked) {
            jumlahTotalHelp.textContent = 'Jumlah unit yang akan dibuat dengan nomor seri unik.';
        } else if (pooledRadio.checked) {
            jumlahTotalHelp.textContent = 'Kapasitas stok maksimal untuk sarana pooled.';
        }
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (preview && previewImg && input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('image');
    const preview = document.getElementById('imagePreview');
    
    if (input && preview) {
        input.value = '';
        preview.style.display = 'none';
    }
}

function confirmRemoveCurrentImage() {
    if (confirm('Apakah Anda yakin ingin menghapus gambar ini? Tindakan ini tidak dapat dibatalkan.')) {
        removeCurrentImage();
    }
}

function removeCurrentImage() {
    const removeInput = document.getElementById('remove_current_image');
    const currentImageContainer = document.querySelector('.current-image');
    
    if (removeInput && currentImageContainer) {
        removeInput.value = '1';
        currentImageContainer.style.display = 'none';
        
        // Show confirmation message
        const message = document.createElement('div');
        message.className = 'alert alert-info';
        message.innerHTML = '<i class="fas fa-info-circle"></i> Gambar akan dihapus saat form disimpan.';
        currentImageContainer.parentNode.insertBefore(message, currentImageContainer.nextSibling);
    }
}

// Delete confirmation functions
function confirmDelete(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    
    if (modal && form) {
        form.action = `/sarana/${id}`;
        modal.style.display = 'flex';
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Unit management functions
function openAddUnitModal() {
    const modal = document.getElementById('addUnitModal');
    const unitCodeInput = document.getElementById('unit_code');
    
    if (modal) {
        modal.style.display = 'flex';
        if (unitCodeInput) {
            unitCodeInput.focus();
        }
    }
}

function closeAddUnitModal() {
    const modal = document.getElementById('addUnitModal');
    const form = document.getElementById('addUnitForm');
    
    if (modal) {
        modal.style.display = 'none';
        if (form) {
            form.reset();
        }
    }
}

function saveUnit() {
    const form = document.getElementById('addUnitForm');
    if (!form) return;
    
    // Validate form
    const unitCode = document.getElementById('unit_code')?.value.trim();
    if (!unitCode) {
        showAlert('Masukkan kode unit terlebih dahulu!', 'warning');
        return;
    }
    
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        showAlert('CSRF token tidak ditemukan', 'error');
        return;
    }
    
    // Show loading state
    const saveButton = document.querySelector('#addUnitModal .btn-primary');
    const originalText = saveButton?.innerHTML;
    if (saveButton) {
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        saveButton.disabled = true;
    }
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const unitCode = document.getElementById('unit_code')?.value || 'Unit';
            showAlert('Unit ' + unitCode + ' berhasil ditambahkan!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = data.message || 'Validasi gagal:';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += '\n• ' + data.errors[field].join(', ');
                });
                showAlert(errorMessage, 'error');
            } else {
                showAlert(data.message || 'Terjadi kesalahan saat menyimpan unit', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat menyimpan unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        if (saveButton) {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        }
    });
}

function editUnit(id, unitCode, status) {
    const modal = document.getElementById('editUnitModal');
    const form = document.getElementById('editUnitForm');
    const unitIdInput = document.getElementById('edit_unit_id');
    const unitCodeInput = document.getElementById('edit_unit_code');
    const statusInput = document.getElementById('edit_unit_status');
    
    if (modal && form && unitIdInput && unitCodeInput && statusInput) {
        form.action = `/sarana/units/${id}`;
        unitIdInput.value = id;
        unitCodeInput.value = unitCode;
        statusInput.value = status;
        modal.style.display = 'flex';
    }
}

function closeEditUnitModal() {
    const modal = document.getElementById('editUnitModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function updateUnit() {
    const form = document.getElementById('editUnitForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const unitId = document.getElementById('edit_unit_id');
    const unitCode = document.getElementById('edit_unit_code');
    const unitStatus = document.getElementById('edit_unit_status');
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!unitId || !unitCode || !unitStatus || !csrfToken) {
        showAlert('Data tidak valid', 'warning');
        return;
    }
    
    if (!unitCode.value.trim()) {
        showAlert('Masukkan kode unit terlebih dahulu!', 'warning');
        return;
    }
    
    if (!unitStatus.value) {
        showAlert('Pilih status unit terlebih dahulu!', 'warning');
        return;
    }
    
    // Show loading state
    const updateButton = document.querySelector('#editUnitModal .btn-primary');
    const originalText = updateButton?.innerHTML;
    if (updateButton) {
        updateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
        updateButton.disabled = true;
    }
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const unitCode = document.getElementById('edit_unit_code')?.value || 'Unit';
            showAlert('Unit ' + unitCode + ' berhasil diperbarui!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = data.message || 'Validasi gagal:';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += '\n• ' + data.errors[field].join(', ');
                });
                showAlert(errorMessage, 'error');
            } else {
                showAlert(data.message || 'Terjadi kesalahan saat mengupdate unit', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat mengupdate unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        if (updateButton) {
            updateButton.innerHTML = originalText;
            updateButton.disabled = false;
        }
    });
}

function confirmDeleteUnit(id, unitCode) {
    const modal = document.getElementById('deleteUnitModal');
    const unitCodeSpan = document.getElementById('deleteUnitCode');
    const form = document.getElementById('deleteUnitForm');
    
    if (modal && unitCodeSpan && form) {
        unitCodeSpan.textContent = unitCode;
        form.action = `/sarana/units/${id}`;
        modal.style.display = 'flex';
    }
}

function closeDeleteUnitModal() {
    const modal = document.getElementById('deleteUnitModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Search and filter functionality for units
function initializeUnitSearch() {
    const searchInput = document.getElementById('unitSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterUnits);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUnits);
    }
}

function filterUnits() {
    const searchTerm = document.getElementById('unitSearch')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const rows = document.querySelectorAll('#unitsTable tbody tr');
    
    rows.forEach(row => {
        const unitCode = row.dataset.unitCode || '';
        const status = row.dataset.status || '';
        
        const matchesSearch = unitCode.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Form validation
function validateSaranaForm() {
    const form = document.getElementById('saranaForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name')?.value.trim();
        const kategoriId = document.getElementById('kategori_id')?.value;
        const type = document.querySelector('input[name="type"]:checked');
        const jumlahTotal = document.getElementById('jumlah_total')?.value;
        
        if (!name) {
            e.preventDefault();
            alert('Nama sarana harus diisi!');
            document.getElementById('name')?.focus();
            return;
        }
        
        if (!kategoriId) {
            e.preventDefault();
            alert('Kategori harus dipilih!');
            document.getElementById('kategori_id')?.focus();
            return;
        }
        
        if (!type) {
            e.preventDefault();
            alert('Tipe sarana harus dipilih!');
            return;
        }
        
        if (!jumlahTotal || parseInt(jumlahTotal) < 1) {
            e.preventDefault();
            alert('Jumlah total harus minimal 1!');
            document.getElementById('jumlah_total')?.focus();
            return;
        }
        
        // Check if serialized and jumlah_total is less than existing units
        const existingUnitsElement = document.querySelector('[data-existing-units]');
        if (existingUnitsElement && type.value === 'serialized') {
            const existingUnits = parseInt(existingUnitsElement.dataset.existingUnits);
            if (parseInt(jumlahTotal) < existingUnits) {
                e.preventDefault();
                alert(`Jumlah total tidak boleh kurang dari ${existingUnits} unit yang sudah ada!`);
                document.getElementById('jumlah_total')?.focus();
                return;
            }
        }
    });
}

// Modal event listeners
function initializeModalEvents() {
    // Close modals when clicking backdrop
    document.querySelectorAll('.dialog-backdrop').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dialog-backdrop').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize type fields
    toggleTypeFields();
    
    // Initialize unit search if on units page
    initializeUnitSearch();
    
    // Initialize form validation
    validateSaranaForm();
    
    // Initialize modal events
    initializeModalEvents();
    
    // Add event listeners for type radio buttons
    const typeRadios = document.querySelectorAll('input[name="type"]');
    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleTypeFields);
    });
    
    // Add event listener for image input
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
});

// Bulk Add Units functions
function openBulkAddModal() {
    const modal = document.getElementById('bulkAddModal');
    const unitCodesInput = document.getElementById('unit_codes');
    
    if (modal) {
        modal.style.display = 'flex';
        if (unitCodesInput) {
            unitCodesInput.focus();
        }
    }
}

function closeBulkAddModal() {
    const modal = document.getElementById('bulkAddModal');
    const form = document.getElementById('bulkAddForm');
    
    if (modal) {
        modal.style.display = 'none';
        if (form) {
            form.reset();
        }
    }
}

function saveBulkUnits() {
    const form = document.getElementById('bulkAddForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    // Parse unit codes from textarea
    const unitCodesText = document.getElementById('unit_codes')?.value.trim();
    if (!unitCodesText) {
        showAlert('Masukkan kode unit terlebih dahulu!', 'warning');
        return;
    }
    
    const unitCodes = unitCodesText.split('\n')
        .map(code => code.trim())
        .filter(code => code.length > 0);
    
    if (unitCodes.length === 0) {
        showAlert('Masukkan kode unit yang valid!', 'warning');
        return;
    }
    
    // Check for duplicates in input
    const uniqueCodes = [...new Set(unitCodes)];
    if (uniqueCodes.length !== unitCodes.length) {
        showAlert('Terdapat kode unit yang duplikat dalam input!', 'warning');
        return;
    }
    
    // Add unit codes to form data
    formData.delete('unit_codes');
    unitCodes.forEach(code => {
        formData.append('unit_codes[]', code);
    });
    
    // Show loading state
    const saveButton = document.querySelector('#bulkAddModal .btn-primary');
    const originalText = saveButton?.innerHTML;
    if (saveButton) {
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        saveButton.disabled = true;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showAlert('CSRF token tidak ditemukan', 'error');
        return;
    }
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(`${unitCodes.length} unit berhasil ditambahkan!`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessage = data.message || 'Validasi gagal:';
                Object.keys(data.errors).forEach(field => {
                    errorMessage += '\n• ' + data.errors[field].join(', ');
                });
                showAlert(errorMessage, 'error');
            } else {
                showAlert(data.message || 'Terjadi kesalahan saat menyimpan unit', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat menyimpan unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        if (saveButton) {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        }
    });
}

// Delete Unit function
function deleteUnit() {
    const form = document.getElementById('deleteUnitForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        showAlert('CSRF token tidak ditemukan', 'error');
        return;
    }
    
    // Show loading state
    const deleteButton = document.querySelector('#deleteUnitModal .btn-danger');
    const originalText = deleteButton?.innerHTML;
    if (deleteButton) {
        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
        deleteButton.disabled = true;
    }
    
    fetch(form.action, {
        method: 'DELETE',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const unitCode = document.getElementById('deleteUnitCode')?.textContent || 'Unit';
            showAlert('Unit ' + unitCode + ' berhasil dihapus!', 'success');
            closeDeleteUnitModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Terjadi kesalahan saat menghapus unit', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat menghapus unit: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        if (deleteButton) {
            deleteButton.innerHTML = originalText;
            deleteButton.disabled = false;
        }
    });
}

// Export functions for global access
window.showAlert = showAlert;
window.toggleTypeFields = toggleTypeFields;
window.previewImage = previewImage;
window.removeImage = removeImage;
window.confirmRemoveCurrentImage = confirmRemoveCurrentImage;
window.removeCurrentImage = removeCurrentImage;
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;
window.openAddUnitModal = openAddUnitModal;
window.closeAddUnitModal = closeAddUnitModal;
window.saveUnit = saveUnit;
window.editUnit = editUnit;
window.closeEditUnitModal = closeEditUnitModal;
window.updateUnit = updateUnit;
window.confirmDeleteUnit = confirmDeleteUnit;
window.closeDeleteUnitModal = closeDeleteUnitModal;
window.deleteUnit = deleteUnit;
window.openBulkAddModal = openBulkAddModal;
window.closeBulkAddModal = closeBulkAddModal;
window.saveBulkUnits = saveBulkUnits;
window.initializeUnitSearch = initializeUnitSearch;
window.filterUnits = filterUnits;
window.validateSaranaForm = validateSaranaForm;
window.initializeModalEvents = initializeModalEvents;
