# Integration JavaScript Modules

This directory contains all JavaScript modules for the integration system. These modules provide client-side functionality for connecting with the various API endpoints.

## File Structure

```
integration/javascript/
├── integration-main.js          # Main entry point and system coordinator
├── integration-manager.js       # Core integration manager
├── integration-dashboard.js     # Main dashboard interface
├── api-client.js               # API communication client
├── front-desk-module.js        # Front desk operations
├── hr-module.js                # Human resources module
├── warehouse-module.js         # Warehouse management
├── integration-styles.css      # Styling for all components
└── README.md                   # This file
```

## Quick Start

### 1. Include the CSS file
```html
<link rel="stylesheet" href="integration/javascript/integration-styles.css">
```

### 2. Include the JavaScript files
```html
<!-- Core modules -->
<script src="integration/javascript/api-client.js"></script>
<script src="integration/javascript/integration-manager.js"></script>
<script src="integration/javascript/integration-dashboard.js"></script>

<!-- Feature modules -->
<script src="integration/javascript/front-desk-module.js"></script>
<script src="integration/javascript/hr-module.js"></script>
<script src="integration/javascript/warehouse-module.js"></script>

<!-- Main entry point -->
<script src="integration/javascript/integration-main.js"></script>
```

### 3. Create a container for the dashboard
```html
<div id="dashboard-container"></div>
```

## Module Overview

### IntegrationMain
The main coordinator that initializes all modules and handles system-wide events.

**Key Features:**
- System initialization and dependency checking
- Global error handling
- Performance monitoring
- Online/offline status management
- Memory usage monitoring

**Usage:**
```javascript
// Access the main integration system
const integration = window.integrationMain;

// Get system status
const status = integration.getSystemStatus();

// Get a specific module
const frontDesk = integration.getModule('frontDesk');
```

### APIClient
Handles all API communications with built-in error handling, retry logic, and caching.

**Key Features:**
- Automatic retry with exponential backoff
- Request queuing for offline scenarios
- Response caching
- Error handling and logging
- Session management

**Usage:**
```javascript
const apiClient = new APIClient();

// Make a request
const result = await apiClient.makeRequest('/api/endpoint', 'POST', data);

// Batch requests
const results = await apiClient.batchRequest([
    { endpoint: '/api/users', method: 'GET' },
    { endpoint: '/api/orders', method: 'GET' }
]);
```

### IntegrationDashboard
Provides the main dashboard interface with tabs for different modules.

**Key Features:**
- Real-time data updates
- Tabbed interface
- Summary cards with metrics
- Charts and analytics
- Export functionality

**Usage:**
```javascript
const dashboard = new IntegrationDashboard();

// Refresh dashboard data
dashboard.refreshDashboard();

// Export data
dashboard.exportData();
```

### FrontDeskModule
Handles front desk operations including guest check-in/out and reservations.

**Key Features:**
- Guest management
- Reservation processing
- Room assignment
- Real-time updates
- Search functionality

**Usage:**
```javascript
const frontDesk = new FrontDeskModule(apiClient);

// Load guest data
await frontDesk.loadGuestData();

// Handle guest check-in
await frontDesk.handleGuestCheckin(guestId);
```

### HRModule
Manages human resources operations including leave requests and employee data.

**Key Features:**
- Employee profile management
- Leave request submission
- Payroll inquiries
- Document uploads
- Attendance tracking

**Usage:**
```javascript
const hr = new HRModule(apiClient);

// Load employee data
await hr.loadEmployeeData();

// Submit leave request
await hr.handleLeaveRequest(event);
```

### WarehouseModule
Handles inventory management and warehouse operations.

**Key Features:**
- Inventory tracking
- Order processing
- Supplier management
- Barcode scanning
- Stock updates
- Reporting

**Usage:**
```javascript
const warehouse = new WarehouseModule(apiClient);

// Load inventory data
await warehouse.loadInventoryData();

// Update stock
await warehouse.handleStockUpdate(data);
```

## API Endpoints

The modules connect to the following API endpoints:

- **Front Desk:** `/integration/core1/front-desk-api.php`
- **HR:** `/integration/hr2/employee-self-service-api.php`
- **Warehouse:** `/integration/log1/smart-warehousing-api.php`
- **Base API:** `/integration/api/base-api.php`

## Configuration

### Session Management
The system automatically handles session tokens:
```javascript
// Set session token
apiClient.setSessionToken('your-token-here');

// Get current token
const token = apiClient.getSessionToken();
```

### Caching
Enable caching for better performance:
```javascript
// Set cache with TTL (time to live)
apiClient.setCache('users', userData, 300000); // 5 minutes

// Get cached data
const cachedData = apiClient.getCache('users');

// Clear cache
apiClient.clearCache('users');
```

## Event System

The integration system uses custom events for communication:

```javascript
// Listen for initialization
document.addEventListener('integration:initialized', (e) => {
    console.log('Integration system ready:', e.detail);
});

// Listen for resize events
document.addEventListener('integration:resize', (e) => {
    console.log('Window resized:', e.detail);
});
```

## Error Handling

The system includes comprehensive error handling:

```javascript
// Global error handler
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
});

// API error handler
apiClient.on('error', (error) => {
    console.error('API error:', error);
});
```

## Performance Monitoring

Built-in performance monitoring tracks:
- Memory usage
- API response times
- Page load times
- Error rates

```javascript
// Get performance metrics
const metrics = integration.getPerformanceMetrics();
console.log('Performance:', metrics);
```

## Offline Support

The system works offline with:
- Request queuing
- Local data caching
- Automatic sync when online

```javascript
// Check online status
if (navigator.onLine) {
    // Process queued requests
    apiClient.processQueue();
}
```

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Dependencies

- Chart.js (for analytics charts)
- Modern browser with ES6+ support

## Development

### Adding New Modules

1. Create a new module class:
```javascript
class NewModule {
    constructor(apiClient) {
        this.apiClient = apiClient;
        this.init();
    }
    
    init() {
        // Initialize module
    }
}
```

2. Register in IntegrationMain:
```javascript
// In initializeModules()
if (typeof NewModule !== 'undefined') {
    this.modules.newModule = new NewModule(this.apiClient);
}
```

### Custom Styling

Override default styles by adding CSS after the main stylesheet:
```css
/* Custom overrides */
.integration-dashboard {
    /* Your custom styles */
}
```

## Troubleshooting

### Common Issues

1. **Module not loading:** Check that all dependencies are included
2. **API errors:** Verify endpoint URLs and authentication
3. **Styling issues:** Ensure CSS file is loaded
4. **Performance issues:** Check browser console for errors

### Debug Mode

Enable debug logging:
```javascript
localStorage.setItem('debug', 'true');
```

### Error Logs

View error logs:
```javascript
const logs = JSON.parse(localStorage.getItem('error_logs') || '[]');
console.log('Error logs:', logs);
```

## Support

For issues or questions:
1. Check browser console for errors
2. Review API endpoint responses
3. Verify module initialization
4. Check network connectivity

## License

This integration system is part of the Admin-final project.
