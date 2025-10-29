/**
 * Peminjaman Detail Page JavaScript
 * Vanilla JS implementation with accessibility features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page
    initPeminjamanDetail();
    initPeminjamanForm();
});

/**
 * Initialize peminjaman detail page
 */
function initPeminjamanDetail() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize image previews
    initImagePreviews();
    
    // Initialize responsive table
    initResponsiveTable();
    
    // Initialize print functionality
    initPrintFunctionality();
    
    // Initialize accessibility features
    initAccessibility();
    
    // Initialize keyboard shortcuts
    initKeyboardShortcuts();
}

/**
 * Initialize create/edit peminjaman form (vanilla JS)
 */
function initPeminjamanForm() {
    const ctx = window.__PEMINJAMAN_FORM_CONTEXT__;
    if (!ctx) return; // Not on form pages

    const container = document.querySelector(ctx.selectors.container);
    const addBtn = document.querySelector(ctx.selectors.addBtn);
    const layout = ctx.layout || 'default';
    const prasaranaSelect = document.getElementById('prasarana_id');
    const lokasiInput = document.getElementById('lokasi_custom');
    const prasaranaGroup = document.getElementById('prasarana_group');
    const lokasiGroup = document.getElementById('lokasi_group');
    const jumlahPesertaInput = document.getElementById('jumlah_peserta');
    const jumlahPesertaGroup = document.getElementById('jumlah_peserta_group');
    const loanTypeHidden = document.getElementById('loan_type_input');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    // Sarana opsional: jangan auto tambah baris

    if (layout !== 'todo') {
        // Hook add button for legacy layout
        if (addBtn && container) {
            const container = document.getElementById('saranaItems');
            const index = container.querySelectorAll('.sarana-todo-item').length;
            addSaranaTodoItem(container, index);
            updateEmptyState();
        }

        // Delegated remove for legacy layout
        if (container) {
            container.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-item')) {
                    const row = e.target.closest('.item-row');
                    if (row) {
                        row.remove(); // sarana opsional: boleh kosong
                    }
                }
            });
        }
    }

    // XOR handling for prasarana vs lokasi_custom
    function updateXorState() {
        const hasPrasarana = prasaranaSelect && prasaranaSelect.value;
        const hasLokasi = lokasiInput && lokasiInput.value.trim().length > 0;
        if (prasaranaSelect) prasaranaSelect.disabled = !!hasLokasi;
        if (lokasiInput) lokasiInput.disabled = !!hasPrasarana;
        // Show/Hide groups
        if (prasaranaGroup) prasaranaGroup.classList.toggle('hidden', !!hasLokasi);
        if (lokasiGroup) lokasiGroup.classList.toggle('hidden', !!hasPrasarana);
        if (jumlahPesertaGroup) {
            jumlahPesertaGroup.style.display = hasPrasarana ? 'block' : 'none';
        }
        if (jumlahPesertaInput) {
            if (hasPrasarana) {
                jumlahPesertaInput.setAttribute('required', 'required');
            } else {
                jumlahPesertaInput.removeAttribute('required');
            }
        }
        updateLoanTypeHidden();
    }
    if (prasaranaSelect) prasaranaSelect.addEventListener('change', updateXorState);
    if (lokasiInput) lokasiInput.addEventListener('input', updateXorState);
    updateXorState();

    // File drop (surat)
    const drop = document.getElementById('suratDrop');
    const input = document.getElementById('surat');
    const preview = document.getElementById('suratPreview');
    if (drop && input && preview) {
        const setPreview = (file) => {
            preview.innerHTML = '';
            if (!file) return;
            const pill = document.createElement('div');
            pill.className = 'file-pill';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'file-thumb';
                img.src = URL.createObjectURL(file);
                pill.appendChild(img);
            }
            const name = document.createElement('span');
            name.textContent = file.name;
            pill.appendChild(name);
            preview.appendChild(pill);
        };
        input.addEventListener('change', function(){
            setPreview(this.files && this.files[0]);
        });
        ;['dragenter','dragover'].forEach(evt => drop.addEventListener(evt, function(e){ e.preventDefault(); drop.classList.add('dragover'); }));
        ;['dragleave','drop'].forEach(evt => drop.addEventListener(evt, function(e){ e.preventDefault(); drop.classList.remove('dragover'); }));
        drop.addEventListener('drop', function(e){
            const dt = e.dataTransfer; if (!dt || !dt.files || !dt.files[0]) return;
            input.files = dt.files; setPreview(dt.files[0]);
        });
    }

    // Duration hint enforcement (UI-only)
    function validateDurationHint() {
        if (!startDate || !endDate) return;
        if (!startDate.value || !endDate.value) return;
        const s = new Date(startDate.value);
        const e = new Date(endDate.value);
        const diff = Math.round((e - s) / (1000*60*60*24)) + 1;
        const hint = container.closest('.card-main').querySelector('.hint');
        if (hint && diff > 0) {
            hint.textContent = hint.textContent.replace(/Maks\s\d+\s*hari/i, function(txt){ return txt; });
        }
    }
    if (startDate) startDate.addEventListener('change', validateDurationHint);
    if (endDate) endDate.addEventListener('change', validateDurationHint);

    function updateLoanTypeHidden() {
        if (!loanTypeHidden) {
            return;
        }
        const hasPrasarana = prasaranaSelect && prasaranaSelect.value;
        const saranaItems = container ? container.querySelectorAll('[name^="sarana_items"]').length > 0 : false;
        if (hasPrasarana && saranaItems) {
            loanTypeHidden.value = 'both';
        } else if (hasPrasarana) {
            loanTypeHidden.value = 'prasarana';
        } else if (saranaItems) {
            loanTypeHidden.value = 'sarana';
        } else {
            loanTypeHidden.value = 'sarana';
        }
    }

    updateLoanTypeHidden();
}

function addItemRow(container, saranaList, index) {
    const row = document.createElement('div');
    row.className = 'item-row';
    row.setAttribute('data-index', index);
    row.innerHTML = `
        <div class="item-top">
            <select name="sarana_items[${index}][sarana_id]" class="form-select sarana-select" required>
                <option value="">Pilih Sarana</option>
                ${saranaList.map(s => `<option value="${s.id}">${escapeHtml(s.name)} (${capitalize(s.type)}) - Tersedia: ${s.jumlah_tersedia ?? 0}</option>`).join('')}
            </select>
            <input type="number" name="sarana_items[${index}][qty_requested]" class="form-input qty-input" value="1" min="1" required>
            <div class="item-actions">
                <button type="button" class="btn btn-danger btn-remove-item">Hapus</button>
            </div>
        </div>
        <div class="item-bottom">
            <input type="text" name="sarana_items[${index}][notes]" class="form-input note-input" placeholder="Catatan (opsional)">
        </div>
    `;
    container.appendChild(row);
    const loanTypeHidden = document.getElementById('loan_type_input');
    if (loanTypeHidden) {
        const event = new Event('change');
        document.getElementById('prasarana_id')?.dispatchEvent(event);
    }
}

function escapeHtml(str){
    return String(str || '').replace(/[&<>"']/g, function(c){
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[c];
    });
}

function capitalize(str){
    if(!str) return '';
    return String(str).charAt(0).toUpperCase() + String(str).slice(1);
}

/**
 * Initialize tooltips for badges and status indicators
 */
function initTooltips() {
    const badges = document.querySelectorAll('.badge');
    
    badges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            showTooltip(this, getTooltipText(this));
        });
        
        badge.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Get tooltip text based on badge content
 */
function getTooltipText(badge) {
    const text = badge.textContent.trim();
    
    const tooltipMap = {
        'Pending': 'Status menunggu persetujuan',
        'Approved': 'Status telah disetujui',
        'Rejected': 'Status ditolak',
        'Partially Approved': 'Status sebagian disetujui',
        'Mahasiswa': 'Pengguna dengan tipe mahasiswa',
        'Staff': 'Pengguna dengan tipe staff',
        'Active': 'Status pengguna aktif',
        'Inactive': 'Status pengguna tidak aktif',
        'Serialized': 'Sarana dengan tipe serialized',
        'Bulk': 'Sarana dengan tipe bulk',
        'Disetujui': 'Item telah disetujui',
        'Pending': 'Item menunggu persetujuan'
    };
    
    return tooltipMap[text] || text;
}

/**
 * Show tooltip
 */
function showTooltip(element, text) {
    hideTooltip();
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
        max-width: 200px;
        word-wrap: break-word;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
    let top = rect.top - tooltipRect.height - 8;
    
    // Adjust if tooltip goes off screen
    if (left < 8) left = 8;
    if (left + tooltipRect.width > window.innerWidth - 8) {
        left = window.innerWidth - tooltipRect.width - 8;
    }
    if (top < 8) {
        top = rect.bottom + 8;
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
    
    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    const existingTooltip = document.querySelector('.tooltip');
    if (existingTooltip) {
        existingTooltip.remove();
    }
}

/**
 * Initialize image previews with zoom functionality
 */
function initImagePreviews() {
    const imagePreviews = document.querySelectorAll('.image-preview__img');
    
    imagePreviews.forEach(img => {
        img.addEventListener('click', function() {
            openImageModal(this.src, this.alt);
        });
        
        // Add cursor pointer and hover effect
        img.style.cursor = 'pointer';
        img.addEventListener('mouseenter', function() {
            this.style.opacity = '0.8';
        });
        img.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
        });
    });
}

/**
 * Open image in modal
 */
function openImageModal(src, alt) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('imagePreview');
    const title = document.getElementById('imageModalTitle');
    
    img.src = src;
    img.alt = alt;
    title.textContent = alt;
    
    modal.classList.add('modal--active');
    modal.setAttribute('aria-hidden', 'false');
    trapFocus(modal);
}

/**
 * Close image modal
 */
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('modal--active');
    modal.setAttribute('aria-hidden', 'true');
    releaseFocus(modal);
}

/**
 * Initialize responsive table functionality
 */
function initResponsiveTable() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const wrapper = table.closest('.table-wrapper');
        if (wrapper) {
            // Add scroll indicator
            addScrollIndicator(wrapper);
            
            // Handle scroll events
            wrapper.addEventListener('scroll', function() {
                updateScrollIndicator(this);
            });
        }
    });
}

/**
 * Add scroll indicator to table wrapper
 */
function addScrollIndicator(wrapper) {
    const indicator = document.createElement('div');
    indicator.className = 'scroll-indicator';
    indicator.style.cssText = `
        position: absolute;
        right: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: #e0e0e0;
        border-radius: 2px;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    wrapper.style.position = 'relative';
    wrapper.appendChild(indicator);
}

/**
 * Update scroll indicator
 */
function updateScrollIndicator(wrapper) {
    const indicator = wrapper.querySelector('.scroll-indicator');
    if (indicator) {
        const scrollLeft = wrapper.scrollLeft;
        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
        
        if (maxScroll > 0) {
            indicator.style.opacity = '1';
            const scrollPercentage = scrollLeft / maxScroll;
            const thumbHeight = (wrapper.clientHeight * wrapper.clientHeight) / wrapper.scrollHeight;
            const thumbTop = scrollPercentage * (wrapper.clientHeight - thumbHeight);
            
            indicator.style.background = scrollLeft > 0 ? '#007bff' : '#e0e0e0';
        } else {
            indicator.style.opacity = '0';
        }
    }
}

/**
 * Initialize print functionality
 */
function initPrintFunctionality() {
    // Add print button if not exists
    const headerActions = document.querySelector('.card-header__actions');
    if (headerActions && !document.querySelector('.print-btn')) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-secondary print-btn';
        printBtn.innerHTML = '<i class="fas fa-print"></i><span>Print</span>';
        printBtn.addEventListener('click', printPage);
        
        headerActions.appendChild(printBtn);
    }
}

/**
 * Print page
 */
function printPage() {
    // Add print-specific styles
    const printStyles = document.createElement('style');
    printStyles.textContent = `
        @media print {
            .card-header__actions,
            .btn:not(.print-btn) {
                display: none !important;
            }
            .page-section {
                background: white !important;
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #e0e0e0 !important;
                margin-bottom: 20px !important;
                page-break-inside: avoid;
            }
            .table {
                min-width: auto !important;
            }
            .modal {
                display: none !important;
            }
        }
    `;
    document.head.appendChild(printStyles);
    
    // Print
    window.print();
    
    // Remove print styles after printing
    setTimeout(() => {
        if (document.head.contains(printStyles)) {
            document.head.removeChild(printStyles);
        }
    }, 1000);
}

/**
 * Initialize accessibility features
 */
function initAccessibility() {
    // Add ARIA labels to interactive elements
    addAriaLabels();
    
    // Initialize keyboard navigation
    initKeyboardNavigation();
    
    // Initialize screen reader announcements
    initScreenReaderAnnouncements();
}

/**
 * Add ARIA labels to interactive elements
 */
function addAriaLabels() {
    // Add labels to badges
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        if (!badge.getAttribute('aria-label')) {
            badge.setAttribute('aria-label', `Status: ${badge.textContent.trim()}`);
        }
    });
    
    // Add labels to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        if (!button.getAttribute('aria-label')) {
            const text = button.textContent.trim();
            button.setAttribute('aria-label', text);
        }
    });
    
    // Add labels to images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (!img.getAttribute('aria-label') && img.alt) {
            img.setAttribute('aria-label', img.alt);
        }
    });
}

/**
 * Initialize keyboard navigation
 */
function initKeyboardNavigation() {
    // Handle keyboard navigation for cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.setAttribute('tabindex', '0');
        card.addEventListener('keydown', function(e) {
            // FIX: Don't trigger keyboard navigation when user is typing in input fields
            const activeElement = document.activeElement;
            const isTyping = activeElement && (
                activeElement.tagName === 'INPUT' || 
                activeElement.tagName === 'TEXTAREA' || 
                activeElement.tagName === 'SELECT'
            );
            
            // Only handle Enter/Space when NOT typing in input fields
            if ((e.key === 'Enter' || e.key === ' ') && !isTyping) {
                e.preventDefault();
                // Focus first interactive element in card
                const firstInteractive = card.querySelector('input, button, a, select, textarea');
                if (firstInteractive) {
                    firstInteractive.focus();
                }
            }
        });
    });
}

/**
 * Initialize screen reader announcements
 */
function initScreenReaderAnnouncements() {
    // Create live region for announcements
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.style.cssText = `
        position: absolute;
        left: -10000px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    `;
    document.body.appendChild(liveRegion);
    
    // Announce status changes
    const statusElements = document.querySelectorAll('[class*="status-"]');
    statusElements.forEach(element => {
        element.addEventListener('change', function() {
            announceToScreenReader(`Status changed to ${this.textContent.trim()}`);
        });
    });
}

/**
 * Initialize keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printPage();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.modal--active');
            if (openModal) {
                if (openModal.id === 'approvalModal') {
                    closeApprovalModal();
                } else if (openModal.id === 'rejectionModal') {
                    closeRejectionModal();
                } else if (openModal.id === 'imageModal') {
                    closeImageModal();
                }
            }
        }
    });
}

/**
 * Announce message to screen readers
 */
function announceToScreenReader(message) {
    const liveRegion = document.querySelector('[aria-live]');
    if (liveRegion) {
        liveRegion.textContent = message;
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 1000);
    }
}

/**
 * Utility function to format dates
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Utility function to format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

/**
 * Utility function to debounce function calls
 */
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

/**
 * Utility function to throttle function calls
 */
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
window.PeminjamanDetail = {
    init: initPeminjamanDetail,
    printPage: printPage,
    formatDate: formatDate,
    formatCurrency: formatCurrency,
    announceToScreenReader: announceToScreenReader,
    openImageModal: openImageModal,
    closeImageModal: closeImageModal
};