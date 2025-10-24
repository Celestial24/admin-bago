/**
 * Integration Manager - Main JavaScript Controller
 * Handles all API integrations and communication between modules
 */

class IntegrationManager {
    constructor() {
        this.baseURL = '/Admin-final/integration';
        this.apiEndpoints = {
            frontDesk: `${this.baseURL}/core1/front-desk-api.php`,
            hrSelfService: `${this.baseURL}/hr2/employee-self-service-api.php`,
            warehousing: `${this.baseURL}/log1/smart-warehousing-api.php`,
            base: `${this.baseURL}/api/base-api.php`
        };
        this.sessionToken = this.getSessionToken();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDashboardData();
        this.initializeModules();
    }

    getSessionToken() {
        return localStorage.getItem('session_token') || sessionStorage.getItem('session_token');
    }

    setSessionToken(token) {
        localStorage.setItem('session_token', token);
        sessionStorage.setItem('session_token', token);
    }

    async makeRequest(endpoint, method = 'GET', data = null) {
        const config = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.sessionToken}`,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(endpoint, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Request failed');
            }

            return result;
        } catch (error) {
            console.error('API Request Error:', error);
            this.showNotification('Error: ' + error.message, 'error');
            throw error;
        }
    }

    async loadDashboardData() {
        try {
            const [frontDeskData, hrData, warehouseData] = await Promise.all([
                this.getFrontDeskData(),
                this.getHRData(),
                this.getWarehouseData()
            ]);

            this.updateDashboard({
                frontDesk: frontDeskData,
                hr: hrData,
                warehouse: warehouseData
            });
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    async getFrontDeskData() {
        return await this.makeRequest(this.apiEndpoints.frontDesk + '?action=get_dashboard_data');
    }

    async getHRData() {
        return await this.makeRequest(this.apiEndpoints.hrSelfService + '?action=get_employee_data');
    }

    async getWarehouseData() {
        return await this.makeRequest(this.apiEndpoints.warehousing + '?action=get_inventory_summary');
    }

    updateDashboard(data) {
        // Update front desk metrics
        if (data.frontDesk?.data) {
            this.updateElement('front-desk-checkins', data.frontDesk.data.today_checkins || 0);
            this.updateElement('front-desk-reservations', data.frontDesk.data.pending_reservations || 0);
            this.updateElement('front-desk-guest-satisfaction', data.frontDesk.data.guest_satisfaction || 'N/A');
        }

        // Update HR metrics
        if (data.hr?.data) {
            this.updateElement('hr-total-employees', data.hr.data.total_employees || 0);
            this.updateElement('hr-active-leaves', data.hr.data.active_leaves || 0);
            this.updateElement('hr-pending-requests', data.hr.data.pending_requests || 0);
        }

        // Update warehouse metrics
        if (data.warehouse?.data) {
            this.updateElement('warehouse-total-items', data.warehouse.data.total_items || 0);
            this.updateElement('warehouse-low-stock', data.warehouse.data.low_stock_items || 0);
            this.updateElement('warehouse-pending-orders', data.warehouse.data.pending_orders || 0);
        }
    }

    updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    setupEventListeners() {
        // Front Desk Events
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="checkin-guest"]')) {
                this.handleGuestCheckin(e.target.dataset.guestId);
            }
            if (e.target.matches('[data-action="process-reservation"]')) {
                this.handleReservationProcess(e.target.dataset.reservationId);
            }
        });

        // HR Events
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="submit-leave-request"]')) {
                this.handleLeaveRequest(e.target.dataset);
            }
            if (e.target.matches('[data-action="update-profile"]')) {
                this.handleProfileUpdate(e.target.dataset);
            }
        });

        // Warehouse Events
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="update-inventory"]')) {
                this.handleInventoryUpdate(e.target.dataset);
            }
            if (e.target.matches('[data-action="process-order"]')) {
                this.handleOrderProcess(e.target.dataset.orderId);
            }
        });
    }

    // Front Desk Methods
    async handleGuestCheckin(guestId) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.frontDesk,
                'POST',
                { action: 'checkin_guest', guest_id: guestId }
            );
            this.showNotification('Guest checked in successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Check-in failed:', error);
        }
    }

    async handleReservationProcess(reservationId) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.frontDesk,
                'POST',
                { action: 'process_reservation', reservation_id: reservationId }
            );
            this.showNotification('Reservation processed successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Reservation processing failed:', error);
        }
    }

    // HR Methods
    async handleLeaveRequest(data) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.hrSelfService,
                'POST',
                { 
                    action: 'submit_leave_request',
                    start_date: data.startDate,
                    end_date: data.endDate,
                    reason: data.reason,
                    leave_type: data.leaveType
                }
            );
            this.showNotification('Leave request submitted successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Leave request failed:', error);
        }
    }

    async handleProfileUpdate(data) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.hrSelfService,
                'PUT',
                { 
                    action: 'update_profile',
                    employee_id: data.employeeId,
                    profile_data: data
                }
            );
            this.showNotification('Profile updated successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Profile update failed:', error);
        }
    }

    // Warehouse Methods
    async handleInventoryUpdate(data) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.warehousing,
                'POST',
                { 
                    action: 'update_inventory',
                    item_id: data.itemId,
                    quantity: data.quantity,
                    operation: data.operation
                }
            );
            this.showNotification('Inventory updated successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Inventory update failed:', error);
        }
    }

    async handleOrderProcess(orderId) {
        try {
            const result = await this.makeRequest(
                this.apiEndpoints.warehousing,
                'POST',
                { action: 'process_order', order_id: orderId }
            );
            this.showNotification('Order processed successfully', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Order processing failed:', error);
        }
    }

    initializeModules() {
        // Initialize Front Desk Module
        if (typeof FrontDeskModule !== 'undefined') {
            new FrontDeskModule(this);
        }

        // Initialize HR Module
        if (typeof HRModule !== 'undefined') {
            new HRModule(this);
        }

        // Initialize Warehouse Module
        if (typeof WarehouseModule !== 'undefined') {
            new WarehouseModule(this);
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);

        // Close button functionality
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    // Utility Methods
    formatDate(date) {
        return new Date(date).toLocaleDateString();
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    debounce(func, wait) {
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
}

// Initialize Integration Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.integrationManager = new IntegrationManager();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IntegrationManager;
}

