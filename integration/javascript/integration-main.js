/**
 * Integration Main - Entry point for all integration modules
 * Initializes and coordinates all integration components
 */

// Import all modules (in a real project, you'd use ES6 imports or a bundler)
// For now, we'll assume all scripts are loaded via script tags

class IntegrationMain {
    constructor() {
        this.modules = {};
        this.apiClient = null;
        this.dashboard = null;
        this.isInitialized = false;
        this.init();
    }

    async init() {
        try {
            console.log('🚀 Initializing Integration System...');
            
            // Check dependencies
            this.checkDependencies();
            
            // Initialize API Client
            this.initializeAPIClient();
            
            // Initialize Dashboard
            this.initializeDashboard();
            
            // Initialize Modules
            await this.initializeModules();
            
            // Setup global event handlers
            this.setupGlobalEventHandlers();
            
            // Start system monitoring
            this.startSystemMonitoring();
            
            this.isInitialized = true;
            console.log('✅ Integration System initialized successfully');
            
            // Dispatch initialization event
            this.dispatchEvent('integration:initialized', { 
                timestamp: new Date().toISOString(),
                modules: Object.keys(this.modules)
            });
            
        } catch (error) {
            console.error('❌ Failed to initialize Integration System:', error);
            this.handleInitializationError(error);
        }
    }

    checkDependencies() {
        const requiredClasses = ['APIClient', 'IntegrationDashboard'];
        const missingDependencies = [];

        requiredClasses.forEach(className => {
            if (typeof window[className] === 'undefined') {
                missingDependencies.push(className);
            }
        });

        if (missingDependencies.length > 0) {
            throw new Error(`Missing required dependencies: ${missingDependencies.join(', ')}`);
        }
    }

    initializeAPIClient() {
        console.log('📡 Initializing API Client...');
        this.apiClient = new APIClient();
        this.modules.apiClient = this.apiClient;
    }

    initializeDashboard() {
        console.log('📊 Initializing Dashboard...');
        this.dashboard = new IntegrationDashboard();
        this.modules.dashboard = this.dashboard;
    }

    async initializeModules() {
        console.log('🔧 Initializing Modules...');
        
        // Initialize Front Desk Module
        if (typeof FrontDeskModule !== 'undefined') {
            this.modules.frontDesk = new FrontDeskModule(this.apiClient);
            console.log('✅ Front Desk Module initialized');
        }

        // Initialize HR Module
        if (typeof HRModule !== 'undefined') {
            this.modules.hr = new HRModule(this.apiClient);
            console.log('✅ HR Module initialized');
        }

        // Initialize Warehouse Module
        if (typeof WarehouseModule !== 'undefined') {
            this.modules.warehouse = new WarehouseModule(this.apiClient);
            console.log('✅ Warehouse Module initialized');
        }

        // Initialize Integration Manager
        if (typeof IntegrationManager !== 'undefined') {
            this.modules.integrationManager = new IntegrationManager();
            console.log('✅ Integration Manager initialized');
        }
    }

    setupGlobalEventHandlers() {
        console.log('🎯 Setting up global event handlers...');
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.handlePageHidden();
            } else {
                this.handlePageVisible();
            }
        });

        // Handle online/offline status
        window.addEventListener('online', () => {
            this.handleOnlineStatus();
        });

        window.addEventListener('offline', () => {
            this.handleOfflineStatus();
        });

        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleWindowResize();
        }, 250));

        // Handle beforeunload
        window.addEventListener('beforeunload', (e) => {
            this.handleBeforeUnload(e);
        });

        // Handle unhandled errors
        window.addEventListener('error', (e) => {
            this.handleGlobalError(e);
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (e) => {
            this.handleUnhandledRejection(e);
        });
    }

    startSystemMonitoring() {
        console.log('📈 Starting system monitoring...');
        
        // Monitor API health
        setInterval(() => {
            this.checkAPIHealth();
        }, 60000); // Check every minute

        // Monitor memory usage
        setInterval(() => {
            this.checkMemoryUsage();
        }, 300000); // Check every 5 minutes

        // Monitor performance
        setInterval(() => {
            this.checkPerformance();
        }, 300000); // Check every 5 minutes
    }

    async checkAPIHealth() {
        try {
            const healthCheck = await this.apiClient.makeRequest(
                this.apiClient.endpoints.base + '?action=health_check'
            );
            
            if (healthCheck.status === 'success') {
                this.updateConnectionStatus('online');
            } else {
                this.updateConnectionStatus('offline');
            }
        } catch (error) {
            this.updateConnectionStatus('offline');
        }
    }

    checkMemoryUsage() {
        if (performance.memory) {
            const memoryInfo = {
                used: Math.round(performance.memory.usedJSHeapSize / 1048576), // MB
                total: Math.round(performance.memory.totalJSHeapSize / 1048576), // MB
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576) // MB
            };

            // Log warning if memory usage is high
            if (memoryInfo.used / memoryInfo.limit > 0.8) {
                console.warn('⚠️ High memory usage detected:', memoryInfo);
                this.showNotification('High memory usage detected', 'warning');
            }
        }
    }

    checkPerformance() {
        const navigation = performance.getEntriesByType('navigation')[0];
        if (navigation) {
            const performanceMetrics = {
                loadTime: Math.round(navigation.loadEventEnd - navigation.loadEventStart),
                domContentLoaded: Math.round(navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart),
                firstPaint: this.getFirstPaintTime()
            };

            // Log performance metrics
            console.log('📊 Performance Metrics:', performanceMetrics);
        }
    }

    getFirstPaintTime() {
        const paintEntries = performance.getEntriesByType('paint');
        const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
        return firstPaint ? Math.round(firstPaint.startTime) : null;
    }

    updateConnectionStatus(status) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            const indicator = statusElement.querySelector('.status-indicator');
            const text = statusElement.querySelector('span:last-child');
            
            if (indicator && text) {
                indicator.className = `status-indicator ${status}`;
                text.textContent = status === 'online' ? 'Connected' : 'Disconnected';
            }
        }
    }

    handlePageHidden() {
        console.log('📱 Page hidden - pausing updates');
        // Pause auto-refresh and other non-essential operations
        if (this.dashboard) {
            this.dashboard.stopAutoRefresh();
        }
    }

    handlePageVisible() {
        console.log('📱 Page visible - resuming updates');
        // Resume auto-refresh and other operations
        if (this.dashboard) {
            this.dashboard.startAutoRefresh();
        }
        
        // Refresh data when page becomes visible
        this.refreshAllData();
    }

    handleOnlineStatus() {
        console.log('🌐 Back online');
        this.updateConnectionStatus('online');
        this.showNotification('Connection restored', 'success');
        
        // Process any queued requests
        if (this.apiClient) {
            this.apiClient.processQueue();
        }
    }

    handleOfflineStatus() {
        console.log('🌐 Gone offline');
        this.updateConnectionStatus('offline');
        this.showNotification('Connection lost - working offline', 'warning');
    }

    handleWindowResize() {
        // Handle responsive layout changes
        if (this.dashboard) {
            // Trigger dashboard resize handling if needed
            this.dispatchEvent('integration:resize', {
                width: window.innerWidth,
                height: window.innerHeight
            });
        }
    }

    handleBeforeUnload(e) {
        // Save any pending data
        this.savePendingData();
        
        // Clean up resources
        this.cleanup();
    }

    handleGlobalError(e) {
        console.error('🚨 Global Error:', e.error);
        this.logError('Global Error', e.error);
    }

    handleUnhandledRejection(e) {
        console.error('🚨 Unhandled Promise Rejection:', e.reason);
        this.logError('Unhandled Promise Rejection', e.reason);
    }

    handleInitializationError(error) {
        console.error('❌ Initialization Error:', error);
        this.showNotification('Failed to initialize integration system', 'error');
        
        // Try to initialize with minimal functionality
        this.initializeMinimalMode();
    }

    initializeMinimalMode() {
        console.log('🔧 Initializing minimal mode...');
        // Initialize only essential components
        this.apiClient = new APIClient();
        this.showNotification('Running in minimal mode', 'warning');
    }

    async refreshAllData() {
        console.log('🔄 Refreshing all data...');
        
        try {
            const refreshPromises = [];
            
            if (this.modules.frontDesk) {
                refreshPromises.push(this.modules.frontDesk.loadGuestData());
            }
            
            if (this.modules.hr) {
                refreshPromises.push(this.modules.hr.loadEmployeeData());
            }
            
            if (this.modules.warehouse) {
                refreshPromises.push(this.modules.warehouse.loadInventoryData());
            }
            
            if (this.dashboard) {
                refreshPromises.push(this.dashboard.loadAllModules());
            }
            
            await Promise.allSettled(refreshPromises);
            console.log('✅ Data refresh completed');
            
        } catch (error) {
            console.error('❌ Data refresh failed:', error);
        }
    }

    savePendingData() {
        console.log('💾 Saving pending data...');
        
        // Save any pending form data
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            if (Object.keys(data).length > 0) {
                localStorage.setItem(`pending_${form.id}`, JSON.stringify(data));
            }
        });
    }

    cleanup() {
        console.log('🧹 Cleaning up resources...');
        
        // Stop all intervals and timeouts
        if (this.dashboard) {
            this.dashboard.stopAutoRefresh();
        }
        
        // Clear any pending requests
        if (this.apiClient) {
            this.apiClient.clearCache();
        }
        
        // Remove event listeners
        this.removeEventListeners();
    }

    removeEventListeners() {
        // Remove global event listeners
        document.removeEventListener('visibilitychange', this.handlePageHidden);
        document.removeEventListener('visibilitychange', this.handlePageVisible);
        window.removeEventListener('online', this.handleOnlineStatus);
        window.removeEventListener('offline', this.handleOfflineStatus);
        window.removeEventListener('resize', this.handleWindowResize);
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        window.removeEventListener('error', this.handleGlobalError);
        window.removeEventListener('unhandledrejection', this.handleUnhandledRejection);
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

    logError(type, error) {
        const errorLog = {
            type,
            message: error.message || error,
            stack: error.stack,
            timestamp: new Date().toISOString(),
            url: window.location.href,
            userAgent: navigator.userAgent
        };

        // Store in localStorage for debugging
        const logs = JSON.parse(localStorage.getItem('error_logs') || '[]');
        logs.push(errorLog);
        
        // Keep only last 50 errors
        if (logs.length > 50) {
            logs.splice(0, logs.length - 50);
        }
        
        localStorage.setItem('error_logs', JSON.stringify(logs));
    }

    dispatchEvent(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        document.dispatchEvent(event);
    }

    // Utility methods
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

    // Public API
    getModule(moduleName) {
        return this.modules[moduleName];
    }

    getAllModules() {
        return this.modules;
    }

    isModuleLoaded(moduleName) {
        return this.modules.hasOwnProperty(moduleName);
    }

    getSystemStatus() {
        return {
            initialized: this.isInitialized,
            modules: Object.keys(this.modules),
            online: navigator.onLine,
            timestamp: new Date().toISOString()
        };
    }
}

// Initialize the integration system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Starting Integration System...');
    window.integrationMain = new IntegrationMain();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IntegrationMain;
}

