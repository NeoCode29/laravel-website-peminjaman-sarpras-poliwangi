/**
 * Dashboard JavaScript - Sistem Peminjaman Sarpras Poliwangi
 * Vanilla JavaScript untuk interaksi dashboard
 */

class DashboardManager {
    constructor() {
        this.mobileMenuToggle = null;
        this.sidebar = null;
        this.sidebarOverlay = null;
        this.mainContent = null;
        this.footer = null;
        this.userBtn = null;
        this.userDropdown = null;
        this.notificationBadge = null;
        this.isSidebarCollapsed = false;
        
        this.init();
    }

    init() {
        this.initializeElements();
        this.bindEvents();
        this.loadDashboardData();
        this.setupAutoRefresh();
    }

    initializeElements() {
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        this.sidebar = document.querySelector('.sidebar');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.mainContent = document.getElementById('mainContent');
        this.footer = document.querySelector('.footer');
        this.userBtn = document.getElementById('userBtn');
        this.userDropdown = document.getElementById('userDropdown');
        this.notificationBadge = document.getElementById('notificationBadge');
    }

    bindEvents() {
        // Mobile menu toggle
        if (this.mobileMenuToggle && this.sidebar) {
            this.mobileMenuToggle.addEventListener('click', () => this.toggleMobileSidebar());
        }
        
        // Sidebar overlay click to close
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => this.closeMobileSidebar());
        }

        // User dropdown toggle
        if (this.userBtn && this.userDropdown) {
            this.userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleUserDropdown();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.userBtn.contains(e.target) && !this.userDropdown.contains(e.target)) {
                    this.userDropdown.classList.remove('show');
                }
            });
        }

        // Submenu toggle
        this.setupSubmenuToggle();

        // Window resize handler
        window.addEventListener('resize', () => this.handleResize());

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && this.sidebar && this.sidebar.classList.contains('show')) {
                if (!this.sidebar.contains(e.target) && !this.mobileMenuToggle.contains(e.target)) {
                    this.closeMobileSidebar();
                }
            }
        });

        // Auto-hide alerts
        this.setupAlertAutoHide();
    }

    toggleMobileSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.toggle('show');
            if (this.sidebarOverlay) {
                this.sidebarOverlay.classList.toggle('show');
            }
        }
    }
    
    closeMobileSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.remove('show');
            if (this.sidebarOverlay) {
                this.sidebarOverlay.classList.remove('show');
            }
        }
    }

    toggleUserDropdown() {
        if (this.userDropdown) {
            this.userDropdown.classList.toggle('show');
        }
    }

    setupSubmenuToggle() {
        const menuToggles = document.querySelectorAll('.menu-toggle');
        
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const menuItem = toggle.closest('.menu-item.has-submenu');
                const submenu = menuItem.querySelector('.submenu');
                
                if (menuItem && submenu) {
                    // Toggle active class on menu item
                    menuItem.classList.toggle('active');
                    
                    // Toggle show class on submenu
                    submenu.classList.toggle('show');
                    
                    // Close other submenus
                    this.closeOtherSubmenus(menuItem);
                }
            });
        });
    }

    closeOtherSubmenus(currentMenuItem) {
        const allMenuItems = document.querySelectorAll('.menu-item.has-submenu');
        
        allMenuItems.forEach(item => {
            if (item !== currentMenuItem) {
                item.classList.remove('active');
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('show');
                }
            }
        });
    }

    handleResize() {
        if (window.innerWidth <= 768) {
            // Mobile: close sidebar and overlay when switching to mobile
            this.closeMobileSidebar();
        } else {
            // Desktop: close mobile sidebar when switching to desktop
            this.closeMobileSidebar();
        }
    }

    setupAlertAutoHide() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                this.hideAlert(alert);
            }, 5000);
        });
    }

    hideAlert(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 300);
    }

    loadDashboardData() {
        this.loadQuickStats();
        this.loadRecentActivities();
        this.loadPendingApprovals();
        this.loadRecentPeminjaman();
    }

    loadQuickStats() {
        // Simulate API call - replace with actual API endpoints
        setTimeout(() => {
            const stats = {
                totalPeminjaman: 24,
                peminjamanAktif: 8,
                totalSarana: 156,
                totalPrasarana: 12
            };

            this.updateElement('totalPeminjaman', stats.totalPeminjaman);
            this.updateElement('peminjamanAktif', stats.peminjamanAktif);
            this.updateElement('totalSarana', stats.totalSarana);
            this.updateElement('totalPrasarana', stats.totalPrasarana);
        }, 500);
    }

    loadRecentActivities() {
        const container = document.getElementById('recentActivities');
        if (!container) return;

        setTimeout(() => {
            const activities = [
                {
                    title: 'Peminjaman Disetujui',
                    text: 'Proyektor LCD untuk acara seminar telah disetujui',
                    time: '2 menit yang lalu',
                    type: 'success'
                },
                {
                    title: 'Marking Dibuat',
                    text: 'Marking untuk Aula Utama pada 25 Desember 2024',
                    time: '15 menit yang lalu',
                    type: 'info'
                },
                {
                    title: 'Peminjaman Ditolak',
                    text: 'Peminjaman kursi untuk acara workshop ditolak karena konflik jadwal',
                    time: '1 jam yang lalu',
                    type: 'danger'
                },
                {
                    title: 'Pengembalian Dikonfirmasi',
                    text: 'Pengembalian meja dan kursi telah dikonfirmasi',
                    time: '2 jam yang lalu',
                    type: 'success'
                }
            ];

            container.innerHTML = activities.map(activity => `
                <div class="timeline-item">
                    <div class="timeline-marker bg-${activity.type === 'success' ? 'success' : activity.type === 'danger' ? 'danger' : 'info'}"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">${activity.title}</div>
                        <div class="timeline-text">${activity.text}</div>
                        <small class="text-muted">${activity.time}</small>
                    </div>
                </div>
            `).join('');
        }, 1000);
    }

    loadPendingApprovals() {
        const container = document.getElementById('pendingApprovals');
        if (!container) return;

        setTimeout(() => {
            const approvals = [
                {
                    id: 1,
                    eventName: 'Seminar Teknologi',
                    requester: 'Ahmad Rizki',
                    date: '25 Des 2024',
                    items: ['Proyektor LCD', 'Aula Utama'],
                    status: 'pending'
                },
                {
                    id: 2,
                    eventName: 'Workshop Programming',
                    requester: 'Siti Nurhaliza',
                    date: '26 Des 2024',
                    items: ['Laptop', 'Lab Komputer'],
                    status: 'pending'
                }
            ];

            if (approvals.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                        <p>Tidak ada peminjaman yang menunggu persetujuan</p>
                    </div>
                `;
            } else {
                container.innerHTML = approvals.map(approval => `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">${approval.eventName}</h6>
                                    <p class="text-muted mb-2">Oleh: ${approval.requester} • ${approval.date}</p>
                                    <div class="mb-2">
                                        ${approval.items.map(item => `<span class="badge bg-light text-dark me-1">${item}</span>`).join('')}
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-success" onclick="dashboardManager.approvePeminjaman(${approval.id})">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="dashboardManager.rejectPeminjaman(${approval.id})">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }, 1500);
    }

    loadRecentPeminjaman() {
        const container = document.getElementById('recentPeminjaman');
        if (!container) return;

        setTimeout(() => {
            const peminjaman = [
                {
                    id: 1,
                    eventName: 'Seminar Teknologi',
                    status: 'approved',
                    date: '25 Des 2024',
                    items: ['Proyektor LCD', 'Aula Utama']
                },
                {
                    id: 2,
                    eventName: 'Workshop Programming',
                    status: 'pending',
                    date: '26 Des 2024',
                    items: ['Laptop', 'Lab Komputer']
                },
                {
                    id: 3,
                    eventName: 'Rapat Koordinasi',
                    status: 'rejected',
                    date: '27 Des 2024',
                    items: ['Meja Rapat', 'Ruang Meeting']
                }
            ];

            container.innerHTML = peminjaman.map(p => `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title mb-1">${p.eventName}</h6>
                                <p class="text-muted mb-2">${p.date}</p>
                                <div class="mb-2">
                                    ${p.items.map(item => `<span class="badge bg-light text-dark me-1">${item}</span>`).join('')}
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-${p.status === 'approved' ? 'success' : p.status === 'pending' ? 'warning' : 'danger'}">
                                    ${p.status === 'approved' ? 'Disetujui' : p.status === 'pending' ? 'Menunggu' : 'Ditolak'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }, 2000);
    }

    setupAutoRefresh() {
        // Auto-refresh every 30 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, 30000);
    }

    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.page-content');
        if (!alertContainer) return;

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} fade-in`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} alert-icon"></i>
            <div>
                <strong>${type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : type === 'warning' ? 'Peringatan!' : 'Info!'}</strong> ${message}
            </div>
        `;
        
        alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hideAlert(alertDiv);
        }, 5000);
    }

    // Action methods
    approvePeminjaman(id) {
        if (confirm('Apakah Anda yakin ingin menyetujui peminjaman ini?')) {
            // Simulate API call
            this.showAlert('Peminjaman berhasil disetujui', 'success');
            this.loadPendingApprovals(); // Refresh the list
        }
    }

    rejectPeminjaman(id) {
        const reason = prompt('Alasan penolakan:');
        if (reason) {
            // Simulate API call
            this.showAlert('Peminjaman ditolak', 'warning');
            this.loadPendingApprovals(); // Refresh the list
        }
    }

    // Utility methods
    formatDate(date) {
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    formatDateTime(date) {
        return new Date(date).toLocaleString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // API methods (to be implemented with actual endpoints)
    async fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching data:', error);
            this.showAlert('Terjadi kesalahan saat memuat data', 'error');
            throw error;
        }
    }

    async postData(url, data) {
        return this.fetchData(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async putData(url, data) {
        return this.fetchData(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteData(url) {
        return this.fetchData(url, {
            method: 'DELETE'
        });
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
});

// Export for use in other scripts
window.DashboardManager = DashboardManager;
