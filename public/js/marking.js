/**
 * Marking JavaScript Functionality
 * Handles marking pages interactions and functionality
 */

// Marking functionality object
const MarkingManager = {
    // Initialize marking functionality
    init: function() {
        this.initLocationToggle();
        this.initPrasaranaInfo();
        this.initDateTimeCalculation();
        this.initSaranaFilter();
        this.initFormValidation();
        this.initModals();
        this.initAutoSubmit();
    },

    // Initialize location type toggle
    initLocationToggle: function() {
        const prasaranaRadio = document.querySelector('input[name="location_type"][value="prasarana"]');
        const customRadio = document.querySelector('input[name="location_type"][value="custom"]');
        
        if (prasaranaRadio && customRadio) {
            prasaranaRadio.addEventListener('change', this.toggleLocationType);
            customRadio.addEventListener('change', this.toggleLocationType);
            this.toggleLocationType();
        }
    },

    // Toggle location type sections
    toggleLocationType: function() {
        const prasaranaSection = document.getElementById('prasarana-section');
        const customSection = document.getElementById('custom-section');
        const prasaranaRadio = document.querySelector('input[name="location_type"][value="prasarana"]');
        const customRadio = document.querySelector('input[name="location_type"][value="custom"]');
        
        if (prasaranaSection && customSection && prasaranaRadio && customRadio) {
            if (prasaranaRadio.checked) {
                prasaranaSection.style.display = 'block';
                customSection.style.display = 'none';
                const prasaranaSelect = document.getElementById('prasarana_id');
                const customInput = document.getElementById('lokasi_custom');
                if (prasaranaSelect) prasaranaSelect.required = true;
                if (customInput) customInput.required = false;
            } else if (customRadio.checked) {
                prasaranaSection.style.display = 'none';
                customSection.style.display = 'block';
                const prasaranaSelect = document.getElementById('prasarana_id');
                const customInput = document.getElementById('lokasi_custom');
                if (prasaranaSelect) prasaranaSelect.required = false;
                if (customInput) customInput.required = true;
            }
        }
    },

    // Initialize prasarana info loading
    initPrasaranaInfo: function() {
        const prasaranaSelect = document.getElementById('prasarana_id');
        if (prasaranaSelect) {
            prasaranaSelect.addEventListener('change', this.loadPrasaranaInfo);
            this.loadPrasaranaInfo();
        }
    },

    // Load prasarana information
    loadPrasaranaInfo: function() {
        const prasaranaSelect = document.getElementById('prasarana_id');
        const selectedOption = prasaranaSelect ? prasaranaSelect.options[prasaranaSelect.selectedIndex] : null;
        const infoSection = document.getElementById('prasarana-info');
        
        if (selectedOption && infoSection) {
            if (selectedOption.value) {
                const kapasitas = selectedOption.getAttribute('data-kapasitas');
                const lokasi = selectedOption.getAttribute('data-lokasi');
                
                const kapasitasSpan = document.getElementById('info-kapasitas');
                const lokasiSpan = document.getElementById('info-lokasi');
                
                if (kapasitasSpan) kapasitasSpan.textContent = kapasitas || '-';
                if (lokasiSpan) lokasiSpan.textContent = lokasi || '-';
                infoSection.style.display = 'block';
            } else {
                infoSection.style.display = 'none';
            }
        }
    },

    // Initialize date time calculation
    initDateTimeCalculation: function() {
        const startDateTime = document.getElementById('start_datetime');
        if (startDateTime) {
            startDateTime.addEventListener('change', this.calculateEndTime);
        }
    },

    // Calculate end time based on start time
    calculateEndTime: function() {
        const startDateTime = document.getElementById('start_datetime');
        const endDateTime = document.getElementById('end_datetime');
        
        if (startDateTime && endDateTime && startDateTime.value) {
            const startDate = new Date(startDateTime.value);
            const endDate = new Date(startDate.getTime() + (2 * 60 * 60 * 1000)); // Default 2 hours
            
            const endDateTimeValue = endDate.toISOString().slice(0, 16);
            endDateTime.value = endDateTimeValue;
        }
    },

    // Initialize sarana filter
    initSaranaFilter: function() {
        const saranaSearch = document.getElementById('sarana_search');
        if (saranaSearch) {
            saranaSearch.addEventListener('keyup', this.filterSarana);
        }
    },

    // Filter sarana list
    filterSarana: function() {
        const searchTerm = document.getElementById('sarana_search');
        const saranaItems = document.querySelectorAll('.sarana-item');
        
        if (searchTerm && saranaItems.length > 0) {
            const searchValue = searchTerm.value.toLowerCase();
            
            saranaItems.forEach(item => {
                const saranaName = item.getAttribute('data-name');
                if (saranaName && saranaName.includes(searchValue)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    },

    // Initialize form validation
    initFormValidation: function() {
        const form = document.getElementById('markingForm');
        if (form) {
            form.addEventListener('submit', this.validateForm);
        }
    },

    // Validate form before submission
    validateForm: function(event) {
        const form = event.target;
        let isValid = true;
        
        // Clear previous errors
        const errorElements = form.querySelectorAll('.form-input-error');
        errorElements.forEach(el => el.classList.remove('form-input-error'));
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('form-input-error');
                isValid = false;
            }
        });
        
        // Validate date time
        const startDateTime = document.getElementById('start_datetime');
        const endDateTime = document.getElementById('end_datetime');
        
        if (startDateTime && endDateTime && startDateTime.value && endDateTime.value) {
            const startDate = new Date(startDateTime.value);
            const endDate = new Date(endDateTime.value);
            
            if (endDate <= startDate) {
                endDateTime.classList.add('form-input-error');
                isValid = false;
            }
        }
        
        // Validate location
        const locationType = form.querySelector('input[name="location_type"]:checked');
        if (locationType) {
            if (locationType.value === 'prasarana') {
                const prasaranaSelect = document.getElementById('prasarana_id');
                if (prasaranaSelect && !prasaranaSelect.value) {
                    prasaranaSelect.classList.add('form-input-error');
                    isValid = false;
                }
            } else if (locationType.value === 'custom') {
                const customInput = document.getElementById('lokasi_custom');
                if (customInput && !customInput.value.trim()) {
                    customInput.classList.add('form-input-error');
                    isValid = false;
                }
            }
        }
        
        if (!isValid) {
            event.preventDefault();
            this.showValidationErrors();
        }
    },

    // Show validation errors
    showValidationErrors: function() {
        const errorFields = document.querySelectorAll('.form-input-error');
        if (errorFields.length > 0) {
            errorFields[0].focus();
            errorFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    },

    // Initialize modals
    initModals: function() {
        // Close modals on backdrop click
        const modals = document.querySelectorAll('.dialog-backdrop');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
        
        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.dialog-backdrop[style*="flex"]');
                if (openModal) {
                    openModal.style.display = 'none';
                }
            }
        });
    },

    // Initialize auto submit for filters
    initAutoSubmit: function() {
        const filterSelects = document.querySelectorAll('.filters-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.form) {
                    this.form.submit();
                }
            });
        });
    }
};

// Modal functions
const MarkingModals = {
    // Delete marking modal
    deleteMarking: function(markingId) {
        window.markingToDelete = markingId;
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    },

    closeDeleteModal: function() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.style.display = 'none';
        }
        window.markingToDelete = null;
    },

    confirmDeleteMarking: function() {
        if (window.markingToDelete) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/marking/${window.markingToDelete}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    },

    // Override marking modal
    overrideMarking: function(markingId) {
        window.markingToOverride = markingId;
        const modal = document.getElementById('overrideModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    },

    closeOverrideModal: function() {
        const modal = document.getElementById('overrideModal');
        if (modal) {
            modal.style.display = 'none';
        }
        window.markingToOverride = null;
    },

    confirmOverrideMarking: function() {
        if (window.markingToOverride) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/marking/${window.markingToOverride}/override`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    },

    // Convert marking modal
    convertToPeminjaman: function(markingId) {
        window.markingToConvert = markingId;
        const modal = document.getElementById('convertModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    },

    closeConvertModal: function() {
        const modal = document.getElementById('convertModal');
        if (modal) {
            modal.style.display = 'none';
        }
        window.markingToConvert = null;
    },

    confirmConvertMarking: function() {
        if (window.markingToConvert) {
            window.location.href = `/peminjaman/create?marking_id=${window.markingToConvert}`;
        }
    }
};

// Utility functions
const MarkingUtils = {
    // Format date for display
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },

    // Format time for display
    formatTime: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Calculate duration between two dates
    calculateDuration: function(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffMs = end - start;
        
        const hours = Math.floor(diffMs / (1000 * 60 * 60));
        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        if (hours > 0) {
            return `${hours} jam ${minutes} menit`;
        } else {
            return `${minutes} menit`;
        }
    },

    // Check if date is in the past
    isPastDate: function(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        return date < now;
    },

    // Check if date is today
    isToday: function(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        return date.toDateString() === today.toDateString();
    },

    // Get relative time
    getRelativeTime: function(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = date - now;
        
        if (diffMs < 0) {
            return 'Sudah lewat';
        }
        
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        if (diffDays > 0) {
            return `${diffDays} hari lagi`;
        } else if (diffHours > 0) {
            return `${diffHours} jam lagi`;
        } else if (diffMinutes > 0) {
            return `${diffMinutes} menit lagi`;
        } else {
            return 'Sekarang';
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    MarkingManager.init();
    
    // Set minimum date to today for date inputs
    const today = new Date().toISOString().slice(0, 16);
    const dateInputs = document.querySelectorAll('input[type="datetime-local"]');
    dateInputs.forEach(input => {
        input.min = today;
    });
});

// Global functions for backward compatibility
function toggleLocationType() {
    MarkingManager.toggleLocationType();
}

function loadPrasaranaInfo() {
    MarkingManager.loadPrasaranaInfo();
}

function calculateEndTime() {
    MarkingManager.calculateEndTime();
}

function filterSarana() {
    MarkingManager.filterSarana();
}

function deleteMarking(markingId) {
    MarkingModals.deleteMarking(markingId);
}

function closeDeleteModal() {
    MarkingModals.closeDeleteModal();
}

function confirmDeleteMarking() {
    MarkingModals.confirmDeleteMarking();
}

function overrideMarking(markingId) {
    MarkingModals.overrideMarking(markingId);
}

function closeOverrideModal() {
    MarkingModals.closeOverrideModal();
}

function confirmOverrideMarking() {
    MarkingModals.confirmOverrideMarking();
}

function convertToPeminjaman(markingId) {
    MarkingModals.convertToPeminjaman(markingId);
}

function closeConvertModal() {
    MarkingModals.closeConvertModal();
}

function confirmConvertMarking() {
    MarkingModals.confirmConvertMarking();
}
