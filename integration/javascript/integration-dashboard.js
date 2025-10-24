/**
 * Integration Dashboard - JavaScript
 * Main dashboard for all integration modules
 */

class IntegrationDashboard {
    constructor() {
        this.apiClient = new APIClient();
        this.modules = {};
        this.dashboardData = {};
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.setupDashboard();
        this.loadAllModules();
        this.setupEventListeners();
        this.startAutoRefresh();
        this.initializeCharts();
    }

    setupDashboard() {
        this.createDashboardHTML();
        this.setupStyles();
    }

    createDashboardHTML() {
        const dashboardHTML = `
            <div class="integration-dashboard">
                <header class="dashboard-header">
                    <h1>Integration Dashboard</h1>
                    <div class="dashboard-controls">
                        <button id="refresh-dashboard" class="btn btn-primary">Refresh</button>
                        <button id="export-data" class="btn btn-secondary">Export Data</button>
                        <div class="connection-status" id="connection-status">
                            <span class="status-indicator online"></span>
                            <span>Connected</span>
                        </div>
                    </div>
                </header>

                <div class="dashboard-content">
                    <!-- Summary Cards -->
                    <div class="summary-cards">
                        <div class="summary-card front-desk">
                            <div class="card-header">
                                <h3>Front Desk</h3>
                                <i class="icon-front-desk">🏨</i>
                            </div>
                            <div class="card-content">
                                <div class="metric">
                                    <span class="metric-label">Today's Check-ins</span>
                                    <span class="metric-value" id="front-desk-checkins">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Pending Reservations</span>
                                    <span class="metric-value" id="front-desk-reservations">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Guest Satisfaction</span>
                                    <span class="metric-value" id="front-desk-guest-satisfaction">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-card hr">
                            <div class="card-header">
                                <h3>Human Resources</h3>
                                <i class="icon-hr">👥</i>
                            </div>
                            <div class="card-content">
                                <div class="metric">
                                    <span class="metric-label">Total Employees</span>
                                    <span class="metric-value" id="hr-total-employees">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Active Leaves</span>
                                    <span class="metric-value" id="hr-active-leaves">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Pending Requests</span>
                                    <span class="metric-value" id="hr-pending-requests">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-card warehouse">
                            <div class="card-header">
                                <h3>Warehouse</h3>
                                <i class="icon-warehouse">📦</i>
                            </div>
                            <div class="card-content">
                                <div class="metric">
                                    <span class="metric-label">Total Items</span>
                                    <span class="metric-value" id="warehouse-total-items">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Low Stock Items</span>
                                    <span class="metric-value" id="warehouse-low-stock">-</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Pending Orders</span>
                                    <span class="metric-value" id="warehouse-pending-orders">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Tabs -->
                    <div class="module-tabs">
                        <nav class="tab-navigation">
                            <button class="tab-btn active" data-tab="front-desk">Front Desk</button>
                            <button class="tab-btn" data-tab="hr">Human Resources</button>
                            <button class="tab-btn" data-tab="warehouse">Warehouse</button>
                            <button class="tab-btn" data-tab="analytics">Analytics</button>
                        </nav>

                        <div class="tab-content">
                            <!-- Front Desk Tab -->
                            <div class="tab-panel active" id="front-desk-panel">
                                <div class="panel-header">
                                    <h2>Front Desk Management</h2>
                                    <div class="panel-actions">
                                        <button class="btn btn-primary" data-action="checkin-guest">New Check-in</button>
                                        <button class="btn btn-secondary" data-action="view-reservations">View Reservations</button>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div id="guests-container" class="data-container"></div>
                                    <div id="reservations-container" class="data-container"></div>
                                </div>
                            </div>

                            <!-- HR Tab -->
                            <div class="tab-panel" id="hr-panel">
                                <div class="panel-header">
                                    <h2>Human Resources</h2>
                                    <div class="panel-actions">
                                        <button class="btn btn-primary" data-action="submit-leave">Submit Leave</button>
                                        <button class="btn btn-secondary" data-action="view-payroll">View Payroll</button>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div id="employee-profile" class="data-container"></div>
                                    <div id="leave-requests-container" class="data-container"></div>
                                </div>
                            </div>

                            <!-- Warehouse Tab -->
                            <div class="tab-panel" id="warehouse-panel">
                                <div class="panel-header">
                                    <h2>Warehouse Management</h2>
                                    <div class="panel-actions">
                                        <button class="btn btn-primary" data-action="update-inventory">Update Inventory</button>
                                        <button class="btn btn-secondary" data-action="process-order">Process Order</button>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div id="inventory-container" class="data-container"></div>
                                    <div id="orders-container" class="data-container"></div>
                                </div>
                            </div>

                            <!-- Analytics Tab -->
                            <div class="tab-panel" id="analytics-panel">
                                <div class="panel-header">
                                    <h2>Analytics & Reports</h2>
                                    <div class="panel-actions">
                                        <button class="btn btn-primary" data-action="generate-report">Generate Report</button>
                                        <button class="btn btn-secondary" data-action="export-analytics">Export Data</button>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="charts-container">
                                        <div class="chart-wrapper">
                                            <canvas id="performance-chart"></canvas>
                                        </div>
                                        <div class="chart-wrapper">
                                            <canvas id="inventory-chart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert dashboard into page
        const container = document.getElementById('dashboard-container') || document.body;
        container.innerHTML = dashboardHTML;
    }

    setupStyles() {
        const styles = `
            <style>
                .integration-dashboard {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: #f5f7fa;
                    min-height: 100vh;
                }

                .dashboard-header {
                    background: white;
                    padding: 1rem 2rem;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .dashboard-controls {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .connection-status {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.9rem;
                }

                .status-indicator {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: #28a745;
                }

                .status-indicator.offline {
                    background: #dc3545;
                }

                .summary-cards {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 1.5rem;
                    padding: 2rem;
                }

                .summary-card {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    overflow: hidden;
                }

                .card-header {
                    padding: 1rem;
                    background: #f8f9fa;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .card-content {
                    padding: 1rem;
                }

                .metric {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 0.5rem;
                }

                .metric-value {
                    font-weight: bold;
                    color: #007bff;
                }

                .module-tabs {
                    margin: 2rem;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .tab-navigation {
                    display: flex;
                    border-bottom: 1px solid #dee2e6;
                }

                .tab-btn {
                    padding: 1rem 2rem;
                    border: none;
                    background: none;
                    cursor: pointer;
                    border-bottom: 3px solid transparent;
                    transition: all 0.3s;
                }

                .tab-btn.active {
                    border-bottom-color: #007bff;
                    color: #007bff;
                    font-weight: bold;
                }

                .tab-panel {
                    display: none;
                    padding: 2rem;
                }

                .tab-panel.active {
                    display: block;
                }

                .panel-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 2rem;
                }

                .panel-actions {
                    display: flex;
                    gap: 1rem;
                }

                .btn {
                    padding: 0.5rem 1rem;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 0.9rem;
                    transition: all 0.3s;
                }

                .btn-primary {
                    background: #007bff;
                    color: white;
                }

                .btn-secondary {
                    background: #6c757d;
                    color: white;
                }

                .btn:hover {
                    opacity: 0.8;
                }

                .data-container {
                    margin-bottom: 2rem;
                }

                .charts-container {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2rem;
                }

                .chart-wrapper {
                    background: #f8f9fa;
                    padding: 1rem;
                    border-radius: 8px;
                }

                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 1rem;
                    border-radius: 4px;
                    color: white;
                    z-index: 1000;
                    max-width: 300px;
                }

                .notification-success {
                    background: #28a745;
                }

                .notification-error {
                    background: #dc3545;
                }

                .notification-warning {
                    background: #ffc107;
                    color: #212529;
                }

                .notification-info {
                    background: #17a2b8;
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }

    async loadAllModules() {
        try {
            // Load all module data in parallel
            const [frontDeskData, hrData, warehouseData] = await Promise.all([
                this.apiClient.getFrontDeskData(),
                this.apiClient.getHRData(),
                this.apiClient.getWarehouseData()
            ]);

            this.dashboardData = {
                frontDesk: frontDeskData,
                hr: hrData,
                warehouse: warehouseData
            };

            this.updateDashboard();
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            this.showNotification('Failed to load dashboard data', 'error');
        }
    }

    updateDashboard() {
        this.updateSummaryCards();
        this.updateModulePanels();
    }

    updateSummaryCards() {
        // Update Front Desk metrics
        if (this.dashboardData.frontDesk?.data) {
            this.updateElement('front-desk-checkins', this.dashboardData.frontDesk.data.today_checkins || 0);
            this.updateElement('front-desk-reservations', this.dashboardData.frontDesk.data.pending_reservations || 0);
            this.updateElement('front-desk-guest-satisfaction', this.dashboardData.frontDesk.data.guest_satisfaction || 'N/A');
        }

        // Update HR metrics
        if (this.dashboardData.hr?.data) {
            this.updateElement('hr-total-employees', this.dashboardData.hr.data.total_employees || 0);
            this.updateElement('hr-active-leaves', this.dashboardData.hr.data.active_leaves || 0);
            this.updateElement('hr-pending-requests', this.dashboardData.hr.data.pending_requests || 0);
        }

        // Update Warehouse metrics
        if (this.dashboardData.warehouse?.data) {
            this.updateElement('warehouse-total-items', this.dashboardData.warehouse.data.total_items || 0);
            this.updateElement('warehouse-low-stock', this.dashboardData.warehouse.data.low_stock_items || 0);
            this.updateElement('warehouse-pending-orders', this.dashboardData.warehouse.data.pending_orders || 0);
        }
    }

    updateModulePanels() {
        // Update Front Desk panel
        if (this.dashboardData.frontDesk?.data) {
            this.updateFrontDeskPanel(this.dashboardData.frontDesk.data);
        }

        // Update HR panel
        if (this.dashboardData.hr?.data) {
            this.updateHRPanel(this.dashboardData.hr.data);
        }

        // Update Warehouse panel
        if (this.dashboardData.warehouse?.data) {
            this.updateWarehousePanel(this.dashboardData.warehouse.data);
        }
    }

    updateFrontDeskPanel(data) {
        const container = document.getElementById('guests-container');
        if (container && data.guests) {
            container.innerHTML = data.guests.map(guest => `
                <div class="guest-item">
                    <h4>${guest.name}</h4>
                    <p>Room: ${guest.room_number}</p>
                    <p>Status: ${guest.status}</p>
                </div>
            `).join('');
        }
    }

    updateHRPanel(data) {
        const container = document.getElementById('employee-profile');
        if (container && data.employee) {
            container.innerHTML = `
                <div class="employee-profile">
                    <h4>${data.employee.name}</h4>
                    <p>Position: ${data.employee.position}</p>
                    <p>Department: ${data.employee.department}</p>
                </div>
            `;
        }
    }

    updateWarehousePanel(data) {
        const container = document.getElementById('inventory-container');
        if (container && data.inventory) {
            container.innerHTML = data.inventory.map(item => `
                <div class="inventory-item">
                    <h4>${item.name}</h4>
                    <p>Stock: ${item.current_stock}</p>
                    <p>Status: ${item.status}</p>
                </div>
            `).join('');
        }
    }

    setupEventListeners() {
        // Tab navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.tab-btn')) {
                this.switchTab(e.target.dataset.tab);
            }
        });

        // Dashboard controls
        document.addEventListener('click', (e) => {
            if (e.target.matches('#refresh-dashboard')) {
                this.refreshDashboard();
            }
            if (e.target.matches('#export-data')) {
                this.exportData();
            }
        });

        // Module actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action]')) {
                this.handleModuleAction(e.target.dataset.action, e.target.dataset);
            }
        });
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.getElementById(`${tabName}-panel`).classList.add('active');
    }

    async handleModuleAction(action, data) {
        switch (action) {
            case 'checkin-guest':
                await this.handleGuestCheckin();
                break;
            case 'submit-leave':
                await this.handleLeaveSubmission();
                break;
            case 'update-inventory':
                await this.handleInventoryUpdate();
                break;
            case 'generate-report':
                await this.generateReport();
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    async handleGuestCheckin() {
        const guestName = prompt('Enter guest name:');
        if (guestName) {
            try {
                await this.apiClient.checkinGuest({ name: guestName });
                this.showNotification('Guest checked in successfully', 'success');
                this.refreshDashboard();
            } catch (error) {
                this.showNotification('Check-in failed', 'error');
            }
        }
    }

    async handleLeaveSubmission() {
        this.showNotification('Leave submission form would open here', 'info');
    }

    async handleInventoryUpdate() {
        this.showNotification('Inventory update form would open here', 'info');
    }

    async generateReport() {
        this.showNotification('Generating report...', 'info');
        // Report generation logic would go here
    }

    refreshDashboard() {
        this.loadAllModules();
    }

    exportData() {
        const data = {
            timestamp: new Date().toISOString(),
            dashboard: this.dashboardData
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `dashboard-export-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
    }

    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.loadAllModules();
        }, 300000); // Refresh every 5 minutes
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    initializeCharts() {
        // Initialize Chart.js charts for analytics
        if (typeof Chart !== 'undefined') {
            this.createPerformanceChart();
            this.createInventoryChart();
        }
    }

    createPerformanceChart() {
        const ctx = document.getElementById('performance-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Performance',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }

    createInventoryChart() {
        const ctx = document.getElementById('inventory-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        data: [300, 50, 100],
                        backgroundColor: [
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(255, 99, 132)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }

    updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.integrationDashboard = new IntegrationDashboard();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IntegrationDashboard;
}
