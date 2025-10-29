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
        this.calendarMonthLabel = null;
        this.calendarPrevBtn = null;
        this.calendarNextBtn = null;
        this.calendarGrid = null;
        this.calendarDetail = null;
        this.isSidebarCollapsed = false;

        this.calendarState = {
            currentDate: new Date(),
            events: [],
            groupedEvents: {},
            selectedDate: null,
        };

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
        this.calendarMonthLabel = document.getElementById('dashboardCalendarMonth');
        this.calendarPrevBtn = document.getElementById('dashboardCalendarPrev');
        this.calendarNextBtn = document.getElementById('dashboardCalendarNext');
        this.calendarGrid = document.getElementById('dashboardCalendarGrid');
        this.calendarDetail = document.getElementById('dashboardCalendarDetail');
        this.statCards = document.querySelectorAll('[data-stat-card]');
        this.yearlyChartContainer = document.getElementById('yearlyLoanChart');
        this.yearlyYearSelect = document.getElementById('yearlyLoanSelect');
        this.yearlyTotalValue = document.querySelector('[data-yearly-total] .loan-trend__meta-value');
        this.yearlyPeakValue = document.querySelector('[data-yearly-peak] .loan-trend__meta-value');
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

        // Calendar navigation
        if (this.calendarPrevBtn) {
            this.calendarPrevBtn.addEventListener('click', () => this.changeCalendarMonth(-1));
        }

        if (this.calendarNextBtn) {
            this.calendarNextBtn.addEventListener('click', () => this.changeCalendarMonth(1));
        }

        if (this.yearlyYearSelect) {
            this.yearlyYearSelect.addEventListener('change', () => {
                const year = this.yearlyYearSelect.value ? parseInt(this.yearlyYearSelect.value, 10) : undefined;
                this.loadYearlyTotals(Number.isInteger(year) ? year : undefined);
            });
        }
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

    async loadYearlyTotals(year) {
        if (!this.yearlyChartContainer) return;

        const params = {};
        if (Number.isInteger(year)) {
            params.year = year;
        }

        this.showYearlyPlaceholder('Memuat data grafik...');

        try {
            const response = await this.fetchData(this.buildUrl('/dashboard/yearly-totals', params));
            this.renderYearlySelect(response.available_years || [], response.year);
            this.renderYearlyMeta(response);
            this.renderYearlyChart(response);
        } catch (error) {
            console.error('Failed to load yearly totals', error);
            this.showYearlyPlaceholder('Gagal memuat data grafik');
        }
    }

    renderYearlySelect(years, selectedYear) {
        if (!this.yearlyYearSelect || !Array.isArray(years)) return;

        const uniqueYears = Array.from(new Set(years)).sort((a, b) => b - a);
        this.yearlyYearSelect.innerHTML = uniqueYears.map((year) => `
            <option value="${year}" ${Number(selectedYear) === Number(year) ? 'selected' : ''}>${year}</option>
        `).join('');
    }

    renderYearlyMeta(data) {
        if (this.yearlyTotalValue) {
            this.yearlyTotalValue.textContent = data?.total ?? 0;
        }

        if (this.yearlyPeakValue) {
            const peakLabel = data?.peak_month;
            this.yearlyPeakValue.textContent = peakLabel ? `${peakLabel} (${data.max})` : '-';
        }
    }

    renderYearlyChart(data) {
        if (!this.yearlyChartContainer) return;

        const values = Array.isArray(data?.data) ? data.data.map((value) => Number(value) || 0) : [];
        const labels = Array.isArray(data?.labels) ? data.labels : [];

        if (!values.length || values.every((value) => value === 0)) {
            this.showYearlyPlaceholder('Belum ada data peminjaman pada tahun ini');
            return;
        }

        const containerWidth = this.yearlyChartContainer.clientWidth || 640;
        const chartWidth = Math.max(containerWidth, 280);
        const isCompact = chartWidth <= 480;
        const height = Math.max(220, Math.min(360, chartWidth * (isCompact ? 0.65 : 0.55)));
        const paddingX = isCompact ? 28 : 40;
        const paddingY = isCompact ? 28 : 40;
        const maxValue = Math.max(...values, 0);
        const effectiveHeight = height - paddingY * 2;
        const effectiveWidth = chartWidth - paddingX * 2;
        const stepX = values.length > 1 ? effectiveWidth / (values.length - 1) : effectiveWidth;

        const points = values.map((value, index) => {
            const x = paddingX + stepX * index;
            const ratio = maxValue === 0 ? 0 : value / maxValue;
            const y = paddingY + (1 - ratio) * effectiveHeight;
            return { x, y };
        });

        const svgNS = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('viewBox', `0 0 ${chartWidth} ${height}`);
        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        svg.classList.add('loan-trend__svg');

        const gridGroup = document.createElementNS(svgNS, 'g');
        gridGroup.setAttribute('stroke', 'rgba(148, 163, 184, 0.25)');
        gridGroup.setAttribute('stroke-width', '1');

        const horizontalLines = 4;
        for (let i = 0; i <= horizontalLines; i += 1) {
            const y = paddingY + (effectiveHeight / horizontalLines) * i;
            const line = document.createElementNS(svgNS, 'line');
            line.setAttribute('x1', String(paddingX));
            line.setAttribute('x2', String(chartWidth - paddingX));
            line.setAttribute('y1', String(y));
            line.setAttribute('y2', String(y));
            gridGroup.appendChild(line);
        }

        svg.appendChild(gridGroup);

        const areaPath = document.createElementNS(svgNS, 'path');
        const linePath = document.createElementNS(svgNS, 'path');

        const pathCommands = points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');
        linePath.setAttribute('d', pathCommands);
        linePath.setAttribute('fill', 'none');
        linePath.setAttribute('stroke', 'rgba(0, 123, 255, 0.85)');
        linePath.setAttribute('stroke-width', '3');
        linePath.setAttribute('stroke-linecap', 'round');

        const lastPoint = points[points.length - 1];
        const firstPoint = points[0];
        const areaCommands = `${pathCommands} L ${lastPoint.x.toFixed(2)} ${height - paddingY} L ${firstPoint.x.toFixed(2)} ${height - paddingY} Z`;
        areaPath.setAttribute('d', areaCommands);
        areaPath.setAttribute('fill', 'rgba(0, 123, 255, 0.15)');

        svg.appendChild(areaPath);
        svg.appendChild(linePath);

        points.forEach((point, index) => {
            const circle = document.createElementNS(svgNS, 'circle');
            circle.setAttribute('cx', point.x.toFixed(2));
            circle.setAttribute('cy', point.y.toFixed(2));
            circle.setAttribute('r', '4');
            circle.setAttribute('fill', '#007BFF');
            svg.appendChild(circle);

            const label = document.createElementNS(svgNS, 'text');
            label.setAttribute('x', point.x.toFixed(2));
            label.setAttribute('y', (height - paddingY / 2).toFixed(2));
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('font-size', isCompact ? '10' : '12');
            label.setAttribute('fill', '#64748b');
            label.textContent = labels[index] ?? `M${index + 1}`;
            svg.appendChild(label);

            const valueLabel = document.createElementNS(svgNS, 'text');
            valueLabel.setAttribute('x', point.x.toFixed(2));
            valueLabel.setAttribute('y', (point.y - 10).toFixed(2));
            valueLabel.setAttribute('text-anchor', 'middle');
            valueLabel.setAttribute('font-size', isCompact ? '11' : '12');
            valueLabel.setAttribute('fill', '#0f172a');
            valueLabel.textContent = values[index];
            svg.appendChild(valueLabel);
        });

        this.yearlyChartContainer.innerHTML = '';
        this.yearlyChartContainer.appendChild(svg);
    }

    showYearlyPlaceholder(message) {
        if (!this.yearlyChartContainer) return;

        this.yearlyChartContainer.innerHTML = `
            <div class="loan-trend__placeholder">
                <i class="fas fa-chart-line"></i>
                <p>${message}</p>
            </div>
        `;
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
        this.loadKpiData();
        this.loadTrendData();
        this.loadRecentActivities();
        this.loadNotifications();
        this.loadCalendarEvents();
        this.loadSnapshotStats();
        this.loadYearlyTotals();
    }

    async loadSnapshotStats() {
        if (!this.statCards.length) return;

        try {
            const stats = await this.fetchData('/dashboard/stats');
            this.renderSnapshotStats(stats);
        } catch (error) {
            console.error('Failed to load dashboard stats', error);
        }
    }

    renderSnapshotStats(stats) {
        if (!stats) return;

        this.statCards.forEach((card) => {
            const type = card.dataset.statCard;
            const target = card.querySelector('[data-stat-value]');
            if (!type || !target) {
                return;
            }

            let value = 0;

            if (type.startsWith('peminjaman.')) {
                const key = type.split('.')[1];
                value = stats?.peminjaman?.[key] ?? 0;
            } else if (type.startsWith('marking.')) {
                const key = type.split('.')[1];
                value = stats?.marking?.[key] ?? 0;
            }

            target.textContent = value;
        });
    }

    async loadKpiData(params = {}) {
        const endpoint = this.buildUrl('/dashboard/kpi', params);
        const kpiCards = {
            active: document.getElementById('kpiActive'),
            pending: document.getElementById('kpiPending'),
            due_today: document.getElementById('kpiDueToday'),
            resources_ready: document.getElementById('kpiResourcesReady'),
            late_percentage: document.getElementById('kpiLatePercentage'),
            average_approval_hours: document.getElementById('kpiAvgApproval'),
            sla_target_hours: document.getElementById('kpiSlaTarget'),
            sla_delta: document.getElementById('kpiSlaDelta'),
        };

        try {
            const response = await this.fetchData(endpoint);
            const data = response.data || {};
            if (kpiCards.active) kpiCards.active.textContent = data.active_loans ?? 0;
            if (kpiCards.pending) kpiCards.pending.textContent = data.pending_loans ?? 0;
            if (kpiCards.due_today) kpiCards.due_today.textContent = data.due_today ?? 0;
            if (kpiCards.resources_ready) kpiCards.resources_ready.textContent = data.resources_ready ?? 0;
            if (kpiCards.late_percentage) kpiCards.late_percentage.textContent = `${data.late_percentage ?? 0}%`;

            if (kpiCards.average_approval_hours) {
                const avg = data.average_approval_hours ?? 0;
                kpiCards.average_approval_hours.textContent = `${avg} jam`;
            }

            if (kpiCards.sla_target_hours) {
                const target = data.sla_target_hours ?? 0;
                kpiCards.sla_target_hours.textContent = `Target SLA: ${target} jam`;
            }

            if (kpiCards.sla_delta) {
                const delta = data.sla_delta ?? 0;
                const label = delta > 0 ? `+${delta} jam` : `${delta} jam`;
                kpiCards.sla_delta.textContent = label;
                kpiCards.sla_delta.dataset.delta = delta;
            }
        } catch (error) {
            console.error('Failed to load KPI data', error);
        }
    }

    async loadTrendData(params = {}) {
        const endpoint = this.buildUrl('/dashboard/trend', params);
        const chartContainer = document.getElementById('dashboardTrendChart');
        const legendContainer = document.getElementById('dashboardTrendLegend');

        if (!chartContainer) return;

        try {
            const response = await this.fetchData(endpoint);
            const labels = response.data?.labels || [];
            const datasets = response.data?.datasets || {};

            this.renderTrendChart(chartContainer, labels, datasets);
            this.renderTrendLegend(legendContainer, datasets);
        } catch (error) {
            console.error('Failed to load trend data', error);
        }
    }

    renderTrendChart(container, labels, datasets) {
        if (!labels.length) {
            container.innerHTML = '<p class="empty-placeholder">Data tren belum tersedia</p>';
            return;
        }

        const colors = {
            submitted: '#1f77b4',
            approved: '#2ca02c',
            rejected: '#d62728',
            marking: '#9467bd',
        };

        const maxValue = Math.max(
            ...Object.values(datasets).flat(),
            1
        );

        const barWidth = Math.max(30, Math.floor(100 / labels.length));

        container.innerHTML = `
            <div class="trend-chart__wrapper">
                ${labels.map((label, index) => `
                    <div class="trend-chart__column" style="width:${barWidth}%">
                        <div class="trend-chart__stack">
                            ${Object.keys(datasets).map(key => {
                                const value = datasets[key][index] || 0;
                                const height = (value / maxValue) * 100;
                                return `<div class="trend-chart__bar" data-key="${key}" style="height:${height}% ; background:${colors[key]};" title="${key} : ${value}"></div>`;
                            }).join('')}
                        </div>
                        <span class="trend-chart__label">${label}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderTrendLegend(container, datasets) {
        if (!container) return;

        const labels = {
            submitted: 'Pengajuan',
            approved: 'Disetujui',
            rejected: 'Ditolak',
            marking: 'Marking',
        };
        const colors = {
            submitted: '#1f77b4',
            approved: '#2ca02c',
            rejected: '#d62728',
            marking: '#9467bd',
        };

        container.innerHTML = Object.keys(datasets).map(key => `
            <span class="trend-legend__item">
                <span class="trend-legend__marker" style="background:${colors[key]}"></span>
                ${labels[key] || key}
            </span>
        `).join('');
    }

    async loadRecentActivities(limit = 10) {
        const container = document.getElementById('recentActivities');
        if (!container) return;

        container.innerHTML = '<div class="empty-placeholder">Memuat aktivitas...</div>';

        try {
            const activities = await this.fetchData(this.buildUrl('/dashboard/activities', { limit }));

            if (!activities.length) {
                container.innerHTML = `
                    <div class="empty-placeholder">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Tidak ada aktivitas terbaru</p>
                    </div>`;
                return;
            }

            container.innerHTML = activities.map(activity => `
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">${activity.action || 'Aktivitas'}</div>
                        <div class="timeline-text">${activity.description || '-'}</div>
                        <div class="timeline-meta">
                            <span>${activity.user?.name ?? 'Sistem'}</span>
                            <span class="timeline-separator">•</span>
                            <span>${this.formatRelativeTime(activity.created_at)}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Failed to load recent activities', error);
        }
    }

    async loadNotifications(limit = 5) {
        const container = document.getElementById('dashboardNotifications');
        const badge = document.getElementById('notificationBadge');
        if (!container) return;

        container.innerHTML = '<div class="empty-placeholder">Memuat notifikasi...</div>';

        try {
            const notifications = await this.fetchData(this.buildUrl('/dashboard/notifications', { limit }));

            if (Array.isArray(notifications) && notifications.length) {
                container.innerHTML = notifications.map(notification => `
                    <div class="notification-item ${notification.is_read ? '' : 'notification-item--unread'}">
                        <div class="notification-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="notification-content">
                            <h4>${notification.title ?? 'Notifikasi'}</h4>
                            <p>${notification.message ?? '-'}</p>
                            <span class="notification-time">${this.formatRelativeTime(notification.created_at)}</span>
                        </div>
                        ${notification.action_url && notification.is_clickable ? `
                            <a href="${notification.action_url}" class="notification-link" title="Lihat detail">
                                <i class="fas fa-chevron-right"></i>
                            </a>` : ''}
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="empty-placeholder">
                        <i class="fas fa-bell-slash"></i>
                        <p>Tidak ada notifikasi terbaru</p>
                    </div>`;
            }

            if (badge) {
                const unreadCount = notifications.filter(n => !n.is_read).length;
                badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                badge.hidden = unreadCount === 0;
            }
        } catch (error) {
            console.error('Failed to load notifications', error);
        }
    }

    async loadCalendarEvents() {
        if (!this.calendarGrid) return;

        const { currentDate } = this.calendarState;
        const start = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const end = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

        const params = {
            start: this.formatDateISO(start),
            end: this.formatDateISO(end),
        };

        this.calendarGrid.innerHTML = '<div class="calendar-placeholder">Memuat kalender...</div>';

        try {
            const events = await this.fetchData(this.buildUrl('/dashboard/calendar', params));
            this.calendarState.events = Array.isArray(events) ? events : [];
            this.calendarState.groupedEvents = this.groupEventsByDate(this.calendarState.events);
            this.renderCalendarMonth();
        } catch (error) {
            console.error('Failed to load calendar events', error);
            this.calendarGrid.innerHTML = '<div class="calendar-placeholder calendar-placeholder--error">Gagal memuat kalender</div>';
        }
    }

    changeCalendarMonth(offset) {
        const current = this.calendarState.currentDate;
        const nextMonth = new Date(current.getFullYear(), current.getMonth() + offset, 1);
        this.calendarState.currentDate = nextMonth;
        this.loadCalendarEvents();
    }

    renderCalendarMonth() {
        if (!this.calendarGrid) return;

        const current = this.calendarState.currentDate;
        if (this.calendarMonthLabel) {
            this.calendarMonthLabel.textContent = current.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
            });
        }

        const startOfMonth = new Date(current.getFullYear(), current.getMonth(), 1);
        const endOfMonth = new Date(current.getFullYear(), current.getMonth() + 1, 0);
        const firstDayIndex = (startOfMonth.getDay() + 6) % 7; // Convert Sunday=0 to Monday=0
        const totalDays = endOfMonth.getDate();
        const totalCells = Math.ceil((firstDayIndex + totalDays) / 7) * 7;

        const weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        let html = '';
        html += weekDays.map(day => `<div class="calendar-day-name">${day}</div>`).join('');

        for (let cellIndex = 0; cellIndex < totalCells; cellIndex += 1) {
            if (cellIndex < firstDayIndex || cellIndex >= firstDayIndex + totalDays) {
                html += '<div class="calendar-day calendar-day--empty"></div>';
                continue;
            }

            const dayNumber = cellIndex - firstDayIndex + 1;
            const dateKey = this.formatDateISO(new Date(current.getFullYear(), current.getMonth(), dayNumber));
            const events = this.calendarState.groupedEvents[dateKey] || [];
            const isSelected = this.calendarState.selectedDate === dateKey;

            html += `
                <button
                    type="button"
                    class="calendar-day${events.length ? ' calendar-day--has-events' : ''}${isSelected ? ' calendar-day--selected' : ''}"
                    data-date="${dateKey}"
                    ${events.length ? '' : 'disabled'}
                >
                    <span class="calendar-day__number">${dayNumber}</span>
                    ${events.length ? `<span class="calendar-day__count">${events.length}</span>` : ''}
                </button>
            `;
        }

        this.calendarGrid.innerHTML = html;

        this.calendarGrid.querySelectorAll('.calendar-day--has-events').forEach((button) => {
            button.addEventListener('click', () => {
                const dateKey = button.dataset.date;
                this.calendarState.selectedDate = dateKey;
                this.renderCalendarMonth();
                this.renderCalendarDetail(dateKey);
            });
        });

        if (this.calendarState.selectedDate) {
            this.renderCalendarDetail(this.calendarState.selectedDate);
        } else {
            this.renderCalendarDetail(null);
        }
    }

    renderCalendarDetail(dateKey) {
        if (!this.calendarDetail) return;

        const detailHeader = this.calendarDetail.querySelector('.calendar-detail__header-title');
        const detailContent = this.calendarDetail.querySelector('.calendar-detail__list');

        if (!detailHeader || !detailContent) return;

        if (!dateKey) {
            detailHeader.textContent = 'Detail Peminjaman';
            detailContent.innerHTML = `
                <div class="calendar-detail__placeholder">
                    <i class="fas fa-calendar-day"></i>
                    <p>Pilih tanggal dengan peminjaman untuk melihat detail.</p>
                </div>`;
            return;
        }

        const events = this.calendarState.groupedEvents[dateKey] || [];
        const dateLabel = new Date(dateKey).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });

        detailHeader.textContent = dateLabel;

        if (!events.length) {
            detailContent.innerHTML = `
                <div class="calendar-detail__placeholder">
                    <i class="fas fa-calendar-times"></i>
                    <p>Tidak ada peminjaman pada tanggal ini.</p>
                </div>`;
            return;
        }

        detailContent.innerHTML = events.map((event) => {
            const statusBadge = `
                <span class="status-badge status-badge--${event.status || 'pending'}">
                    ${this.formatStatusLabel(event.status)}
                </span>`;

            const actionLink = event.url ? `
                <a class="calendar-detail__link" href="${event.url}">
                    Detail acara
                    <i class="fas fa-arrow-right"></i>
                </a>` : '';

            return `
                <div class="calendar-detail__item">
                    <div class="calendar-detail__info">
                        <h4>${event.title || 'Agenda'}</h4>
                        <div class="calendar-detail__meta">
                            <span><i class="fas fa-clock"></i> ${this.formatEventTime(event)}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${event.location || 'Lokasi belum ditentukan'}</span>
                        </div>
                        ${actionLink}
                    </div>
                    ${statusBadge}
                </div>`;
        }).join('');
    }

    groupEventsByDate(events) {
        return events.reduce((accumulator, event) => {
            const dateKey = event.start ? event.start.substring(0, 10) : null;
            if (!dateKey) {
                return accumulator;
            }

            if (!accumulator[dateKey]) {
                accumulator[dateKey] = [];
            }

            accumulator[dateKey].push(event);
            return accumulator;
        }, {});
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

    buildUrl(path, params = {}) {
        const url = new URL(path, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        return url.toString();
    }

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

    formatRelativeTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '-';

        const diffSeconds = (Date.now() - date.getTime()) / 1000;
        if (diffSeconds < 60) return `${Math.floor(diffSeconds)} detik lalu`;
        if (diffSeconds < 3600) return `${Math.floor(diffSeconds / 60)} menit lalu`;
        if (diffSeconds < 86400) return `${Math.floor(diffSeconds / 3600)} jam lalu`;
        return `${Math.floor(diffSeconds / 86400)} hari lalu`;
    }

    formatDatePart(value, part) {
        if (!value) return '--';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '--';

        if (part === 'day') {
            return date.getDate().toString().padStart(2, '0');
        }

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return monthNames[date.getMonth()] ?? '--';
    }

    formatStatusLabel(status) {
        const labels = {
            pending: 'Menunggu',
            approved: 'Disetujui',
            completed: 'Selesai',
            rejected: 'Ditolak',
            marking: 'Marking',
        };
        return labels[status] || (status ? status.replace('_', ' ') : 'Menunggu');
    }

    formatEventTime(event) {
        const formatClock = (value) => {
            if (!value) return null;

            if (value instanceof Date && !Number.isNaN(value.getTime())) {
                return value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }

            if (typeof value === 'string') {
                const parts = value.split(':');
                if (parts.length >= 2) {
                    const hours = Number.parseInt(parts[0], 10);
                    const minutes = Number.parseInt(parts[1], 10);

                    if (!Number.isNaN(hours) && !Number.isNaN(minutes)) {
                        const date = new Date();
                        date.setHours(hours, minutes, 0, 0);
                        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    }
                }
            }

            return null;
        };

        const startLabelFromFields = formatClock(event.start_time);
        const endLabelFromFields = formatClock(event.end_time);

        if (startLabelFromFields || endLabelFromFields) {
            if (startLabelFromFields && endLabelFromFields) {
                return `${startLabelFromFields} - ${endLabelFromFields}`;
            }
            return startLabelFromFields || endLabelFromFields;
        }

        if (!event.start && !event.end) {
            return 'Waktu belum ditentukan';
        }

        const start = event.start ? new Date(event.start) : null;
        const end = event.end ? new Date(event.end) : null;

        if (start && end && !Number.isNaN(start.getTime()) && !Number.isNaN(end.getTime())) {
            const startLabel = formatClock(start);
            const endLabel = formatClock(end);
            if (startLabel && endLabel) {
                return `${startLabel} - ${endLabel}`;
            }
        }

        if (start && !Number.isNaN(start.getTime())) {
            const label = formatClock(start);
            if (label) {
                return label;
            }
        }

        return 'Waktu belum ditentukan';
    }

    formatDateISO(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
            return '';
        }

        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
});

// Export for use in other scripts
window.DashboardManager = DashboardManager;
