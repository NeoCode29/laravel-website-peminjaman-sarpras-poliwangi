/**
 * Kategori Prasarana JavaScript
 * Handles all interactions for kategori prasarana pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all kategori prasarana functionality
    initKategoriPrasarana();
});

function initKategoriPrasarana() {
    // Initialize form validation
    initFormValidation();
    
    // Initialize preview functionality
    initPreviewFunctionality();
    
    // Initialize filter functionality
    initFilterFunctionality();
    
    // Initialize delete confirmation
    initDeleteConfirmation();
    
    // Initialize auto-save functionality
    initAutoSave();
    
    // Initialize responsive behavior
    initResponsiveBehavior();
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.form-card');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('.form-input[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.getAttribute('name');
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = `${getFieldLabel(field)} harus diisi!`;
        isValid = false;
    }
    
    // Name field validation
    if (fieldName === 'name' && value) {
        if (value.length < 3) {
            errorMessage = 'Nama kategori minimal 3 karakter!';
            isValid = false;
        } else if (value.length > 100) {
            errorMessage = 'Nama kategori maksimal 100 karakter!';
            isValid = false;
        } else if (!/^[a-zA-Z0-9\s\-_]+$/.test(value)) {
            errorMessage = 'Nama kategori hanya boleh mengandung huruf, angka, spasi, tanda hubung, dan underscore!';
            isValid = false;
        }
    }
    
    // Description field validation
    if (fieldName === 'description' && value && value.length > 500) {
        errorMessage = 'Deskripsi maksimal 500 karakter!';
        isValid = false;
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function getFieldLabel(field) {
    const label = field.closest('.form-group').querySelector('.form-label');
    return label ? label.textContent.replace('*', '').trim() : 'Field';
}

function showFieldError(field, message) {
    field.classList.add('form-input-error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    
    const formGroup = field.closest('.form-group');
    formGroup.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('form-input-error');
    
    const errorDiv = field.closest('.form-group').querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Preview Functionality
 */
function initPreviewFunctionality() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('previewName');
    const previewDescription = document.getElementById('previewDescription');
    
    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            updatePreview('name', this.value.trim());
        });
    }
    
    if (descriptionInput && previewDescription) {
        descriptionInput.addEventListener('input', function() {
            updatePreview('description', this.value.trim());
        });
    }
}

function updatePreview(type, value) {
    const previewElement = document.getElementById(`preview${type.charAt(0).toUpperCase() + type.slice(1)}`);
    
    if (previewElement) {
        previewElement.textContent = value || '-';
    }
}

/**
 * Filter Functionality
 */
function initFilterFunctionality() {
    const filterForm = document.querySelector('.filters-form');
    if (!filterForm) return;
    
    const filterSelects = document.querySelectorAll('.filters-select');
    const searchInput = document.querySelector('.search-input');
    
    // Auto-submit when select changes
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            submitFilterForm();
        });
    });
    
    // Auto-submit when search input changes (with debounce)
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                submitFilterForm();
            }, 500); // 500ms delay
        });
    }
}

function submitFilterForm() {
    const filterForm = document.querySelector('.filters-form');
    if (filterForm) {
        filterForm.submit();
    }
}

/**
 * Delete Confirmation
 */
function initDeleteConfirmation() {
    // This is handled by the inline functions in the Blade templates
    // but we can add additional functionality here if needed
}

function confirmDelete(id, name) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const nameElement = document.getElementById('deleteCategoryName');
    
    if (nameElement) {
        nameElement.textContent = name;
    }
    
    if (form) {
        form.action = `/kategori-prasarana/${id}`;
    }
    
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal when clicking backdrop
document.addEventListener('click', function(e) {
    const modal = document.getElementById('deleteModal');
    if (modal && e.target === modal) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

/**
 * Auto-save Functionality
 */
function initAutoSave() {
    const forms = document.querySelectorAll('.form-card');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('.form-input');
        let hasChanges = false;
        
        // Track changes
        inputs.forEach(input => {
            const originalValue = input.value;
            
            input.addEventListener('input', function() {
                hasChanges = this.value !== originalValue;
                updateSaveStatus(form, hasChanges);
            });
        });
        
        // Warn before leaving if there are unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman?';
            }
        });
    });
}

function updateSaveStatus(form, hasChanges) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        if (hasChanges) {
            submitButton.classList.add('btn-warning');
            submitButton.classList.remove('btn-primary');
        } else {
            submitButton.classList.add('btn-primary');
            submitButton.classList.remove('btn-warning');
        }
    }
}

/**
 * Responsive Behavior
 */
function initResponsiveBehavior() {
    // Handle mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
        });
    }
    
    // Handle table responsiveness
    const tableWrapper = document.querySelector('.table-wrapper');
    if (tableWrapper) {
        // Add horizontal scroll indicator
        if (tableWrapper.scrollWidth > tableWrapper.clientWidth) {
            tableWrapper.classList.add('has-horizontal-scroll');
        }
    }
    
    // Handle form grid responsiveness
    const formGrid = document.querySelector('.form-grid');
    if (formGrid) {
        handleFormGridResponsiveness(formGrid);
    }
}

function handleFormGridResponsiveness(formGrid) {
    function updateFormGrid() {
        if (window.innerWidth <= 768) {
            formGrid.style.gridTemplateColumns = '1fr';
        } else {
            formGrid.style.gridTemplateColumns = '1fr 1fr';
        }
    }
    
    updateFormGrid();
    window.addEventListener('resize', updateFormGrid);
}

/**
 * Utility Functions
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-in`;
    
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    const title = {
        'success': 'Berhasil!',
        'error': 'Error!',
        'warning': 'Peringatan!',
        'info': 'Info!'
    }[type] || 'Info!';
    
    alert.innerHTML = `
        <i class="fas ${iconClass} alert-icon"></i>
        <div>
            <strong>${title}</strong> ${message}
        </div>
    `;
    
    // Insert alert after content header
    const contentHeader = document.querySelector('.content-header');
    if (contentHeader) {
        contentHeader.insertAdjacentElement('afterend', alert);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 300);
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Export functions for global use
 */
window.KategoriPrasarana = {
    confirmDelete,
    closeDeleteModal,
    showAlert,
    debounce,
    throttle
};


