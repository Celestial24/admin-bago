/**
 * API Client - JavaScript Integration
 * Handles all API communications and data management
 */

class APIClient {
    constructor() {
        this.baseURL = '/Admin-final/integration';
        this.endpoints = {
            frontDesk: `${this.baseURL}/core1/front-desk-api.php`,
            hrSelfService: `${this.baseURL}/hr2/employee-self-service-api.php`,
            warehousing: `${this.baseURL}/log1/smart-warehousing-api.php`,
            base: `${this.baseURL}/api/base-api.php`
        };
        this.sessionToken = this.getSessionToken();
        this.requestQueue = [];
        this.isProcessingQueue = false;
        this.init();
    }

    init() {
        this.setupInterceptors();
        this.setupErrorHandling();
        this.setupRetryMechanism();
    }

    getSessionToken() {
        return localStorage.getItem('session_token') || 
               sessionStorage.getItem('session_token') || 
               this.generateTempToken();
    }

    generateTempToken() {
        const tempToken = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('session_token', tempToken);
        return tempToken;
    }

    setSessionToken(token) {
        localStorage.setItem('session_token', token);
        sessionStorage.setItem('session_token', token);
        this.sessionToken = token;
    }

    async makeRequest(endpoint, method = 'GET', data = null, options = {}) {
        const requestId = this.generateRequestId();
        const request = {
            id: requestId,
            endpoint,
            method,
            data,
            options,
            timestamp: Date.now()
        };

        // Add to queue if offline or rate limiting
        if (this.shouldQueueRequest()) {
            this.requestQueue.push(request);
            return this.processQueuedRequest(requestId);
        }

        return this.executeRequest(request);
    }

    async executeRequest(request) {
        const config = {
            method: request.method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.sessionToken}`,
                'X-Requested-With': 'XMLHttpRequest',
                'X-Request-ID': request.id
            },
            ...request.options
        };

        if (request.data && (request.method === 'POST' || request.method === 'PUT')) {
            config.body = JSON.stringify(request.data);
        }

        try {
            const response = await fetch(request.endpoint, config);
            const result = await response.json();

            if (!response.ok) {
                throw new APIError(result.message || 'Request failed', response.status, result);
            }

            // Log successful request
            this.logRequest(request, result, 'success');
            return result;

        } catch (error) {
            // Log failed request
            this.logRequest(request, error, 'error');
            
            // Handle retry logic
            if (this.shouldRetry(error, request)) {
                return this.retryRequest(request);
            }

            throw error;
        }
    }

    async processQueuedRequest(requestId) {
        return new Promise((resolve, reject) => {
            const checkQueue = () => {
                const request = this.requestQueue.find(r => r.id === requestId);
                if (request && request.result) {
                    if (request.result.status === 'success') {
                        resolve(request.result);
                    } else {
                        reject(request.result.error);
                    }
                } else {
                    setTimeout(checkQueue, 100);
                }
            };
            checkQueue();
        });
    }

    async processQueue() {
        if (this.isProcessingQueue || this.requestQueue.length === 0) {
            return;
        }

        this.isProcessingQueue = true;

        while (this.requestQueue.length > 0) {
            const request = this.requestQueue.shift();
            try {
                const result = await this.executeRequest(request);
                request.result = { status: 'success', data: result };
            } catch (error) {
                request.result = { status: 'error', error };
            }
        }

        this.isProcessingQueue = false;
    }

    shouldQueueRequest() {
        return !navigator.onLine || this.isRateLimited();
    }

    isRateLimited() {
        const now = Date.now();
        const recentRequests = this.getRecentRequests(now - 60000); // Last minute
        return recentRequests.length > 60; // Max 60 requests per minute
    }

    getRecentRequests(since) {
        return this.requestQueue.filter(req => req.timestamp > since);
    }

    shouldRetry(error, request) {
        if (request.retryCount >= 3) return false;
        if (error.status >= 400 && error.status < 500) return false;
        return true;
    }

    async retryRequest(request) {
        request.retryCount = (request.retryCount || 0) + 1;
        const delay = Math.pow(2, request.retryCount) * 1000; // Exponential backoff
        
        await new Promise(resolve => setTimeout(resolve, delay));
        return this.executeRequest(request);
    }

    setupInterceptors() {
        // Request interceptor
        this.addRequestInterceptor((config) => {
            // Add timestamp to all requests
            config.headers['X-Timestamp'] = Date.now().toString();
            return config;
        });

        // Response interceptor
        this.addResponseInterceptor((response) => {
            // Update session token if provided
            if (response.headers['X-New-Token']) {
                this.setSessionToken(response.headers['X-New-Token']);
            }
            return response;
        });
    }

    setupErrorHandling() {
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason instanceof APIError) {
                this.handleAPIError(event.reason);
            }
        });
    }

    setupRetryMechanism() {
        // Process queue when back online
        window.addEventListener('online', () => {
            this.processQueue();
        });
    }

    handleAPIError(error) {
        switch (error.status) {
            case 401:
                this.handleUnauthorized();
                break;
            case 403:
                this.handleForbidden();
                break;
            case 429:
                this.handleRateLimit();
                break;
            case 500:
                this.handleServerError();
                break;
            default:
                this.handleGenericError(error);
        }
    }

    handleUnauthorized() {
        this.showNotification('Session expired. Please login again.', 'error');
        setTimeout(() => {
            window.location.href = '/login.php';
        }, 2000);
    }

    handleForbidden() {
        this.showNotification('Access denied. Insufficient permissions.', 'error');
    }

    handleRateLimit() {
        this.showNotification('Too many requests. Please wait a moment.', 'warning');
    }

    handleServerError() {
        this.showNotification('Server error. Please try again later.', 'error');
    }

    handleGenericError(error) {
        this.showNotification(`Error: ${error.message}`, 'error');
    }

    // Specific API methods
    async getFrontDeskData() {
        return this.makeRequest(`${this.endpoints.frontDesk}?action=get_dashboard_data`);
    }

    async getHRData() {
        return this.makeRequest(`${this.endpoints.hrSelfService}?action=get_employee_data`);
    }

    async getWarehouseData() {
        return this.makeRequest(`${this.endpoints.warehousing}?action=get_inventory_summary`);
    }

    async checkinGuest(guestData) {
        return this.makeRequest(this.endpoints.frontDesk, 'POST', {
            action: 'checkin_guest',
            ...guestData
        });
    }

    async submitLeaveRequest(leaveData) {
        return this.makeRequest(this.endpoints.hrSelfService, 'POST', {
            action: 'submit_leave_request',
            ...leaveData
        });
    }

    async updateInventory(inventoryData) {
        return this.makeRequest(this.endpoints.warehousing, 'POST', {
            action: 'update_inventory',
            ...inventoryData
        });
    }

    // Utility methods
    generateRequestId() {
        return 'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    logRequest(request, result, status) {
        const logEntry = {
            id: request.id,
            endpoint: request.endpoint,
            method: request.method,
            status,
            timestamp: new Date().toISOString(),
            duration: Date.now() - request.timestamp
        };

        // Store in localStorage for debugging
        const logs = JSON.parse(localStorage.getItem('api_logs') || '[]');
        logs.push(logEntry);
        
        // Keep only last 100 logs
        if (logs.length > 100) {
            logs.splice(0, logs.length - 100);
        }
        
        localStorage.setItem('api_logs', JSON.stringify(logs));
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `api-notification api-notification-${type}`;
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

    // Interceptor management
    addRequestInterceptor(interceptor) {
        if (!this.requestInterceptors) {
            this.requestInterceptors = [];
        }
        this.requestInterceptors.push(interceptor);
    }

    addResponseInterceptor(interceptor) {
        if (!this.responseInterceptors) {
            this.responseInterceptors = [];
        }
        this.responseInterceptors.push(interceptor);
    }

    // Batch operations
    async batchRequest(requests) {
        const promises = requests.map(request => this.makeRequest(
            request.endpoint,
            request.method,
            request.data,
            request.options
        ));

        return Promise.allSettled(promises);
    }

    // Cache management
    setCache(key, data, ttl = 300000) { // 5 minutes default
        const cacheEntry = {
            data,
            timestamp: Date.now(),
            ttl
        };
        localStorage.setItem(`cache_${key}`, JSON.stringify(cacheEntry));
    }

    getCache(key) {
        const cacheEntry = localStorage.getItem(`cache_${key}`);
        if (!cacheEntry) return null;

        const parsed = JSON.parse(cacheEntry);
        if (Date.now() - parsed.timestamp > parsed.ttl) {
            localStorage.removeItem(`cache_${key}`);
            return null;
        }

        return parsed.data;
    }

    clearCache(pattern = null) {
        if (pattern) {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('cache_') && key.includes(pattern)) {
                    localStorage.removeItem(key);
                }
            });
        } else {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('cache_')) {
                    localStorage.removeItem(key);
                }
            });
        }
    }
}

// Custom Error Class
class APIError extends Error {
    constructor(message, status, response) {
        super(message);
        this.name = 'APIError';
        this.status = status;
        this.response = response;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { APIClient, APIError };
}
