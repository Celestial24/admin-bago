/**
 * Warehouse Module - JavaScript Integration
 * Handles smart warehousing and inventory management
 */

class WarehouseModule {
    constructor(integrationManager) {
        this.manager = integrationManager;
        this.apiEndpoint = integrationManager.apiEndpoints.warehousing;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInventoryData();
        this.loadOrders();
        this.loadSuppliers();
        this.initializeBarcodeScanner();
        this.startRealTimeUpdates();
    }

    setupEventListeners() {
        // Inventory management
        const inventoryForm = document.getElementById('inventory-form');
        if (inventoryForm) {
            inventoryForm.addEventListener('submit', (e) => this.handleInventoryUpdate(e));
        }

        // Order processing
        const orderForm = document.getElementById('order-form');
        if (orderForm) {
            orderForm.addEventListener('submit', (e) => this.handleOrderCreate(e));
        }

        // Supplier management
        const supplierForm = document.getElementById('supplier-form');
        if (supplierForm) {
            supplierForm.addEventListener('submit', (e) => this.handleSupplierUpdate(e));
        }

        // Action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="update-stock"]')) {
                this.handleStockUpdate(e.target.dataset);
            }
            if (e.target.matches('[data-action="process-order"]')) {
                this.handleOrderProcess(e.target.dataset.orderId);
            }
            if (e.target.matches('[data-action="scan-barcode"]')) {
                this.handleBarcodeScan();
            }
            if (e.target.matches('[data-action="generate-report"]')) {
                this.handleGenerateReport(e.target.dataset);
            }
        });

        // Search functionality
        const searchInput = document.getElementById('inventory-search');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => this.searchInventory(e.target.value), 300));
        }
    }

    async loadInventoryData() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_inventory`);
            this.displayInventory(result.data);
        } catch (error) {
            console.error('Failed to load inventory data:', error);
        }
    }

    async loadOrders() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_orders`);
            this.displayOrders(result.data);
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    }

    async loadSuppliers() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_suppliers`);
            this.displaySuppliers(result.data);
        } catch (error) {
            console.error('Failed to load suppliers:', error);
        }
    }

    displayInventory(inventory) {
        const container = document.getElementById('inventory-container');
        if (!container) return;

        container.innerHTML = inventory.map(item => `
            <div class="inventory-item" data-item-id="${item.id}">
                <div class="item-info">
                    <h3>${item.name}</h3>
                    <p>SKU: ${item.sku}</p>
                    <p>Category: ${item.category}</p>
                    <p>Location: ${item.location}</p>
                </div>
                <div class="item-stock">
                    <div class="stock-level ${this.getStockLevelClass(item.current_stock, item.min_stock)}">
                        <span>Current Stock: ${item.current_stock}</span>
                        <span>Min Stock: ${item.min_stock}</span>
                    </div>
                    <div class="stock-actions">
                        <button class="btn btn-primary" data-action="update-stock" data-item-id="${item.id}">Update Stock</button>
                        <button class="btn btn-info" data-action="view-item-details" data-item-id="${item.id}">View Details</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    displayOrders(orders) {
        const container = document.getElementById('orders-container');
        if (!container) return;

        container.innerHTML = orders.map(order => `
            <div class="order-card" data-order-id="${order.id}">
                <div class="order-info">
                    <h3>Order #${order.order_number}</h3>
                    <p>Supplier: ${order.supplier_name}</p>
                    <p>Date: ${this.manager.formatDate(order.order_date)}</p>
                    <p>Total: ${this.manager.formatCurrency(order.total_amount)}</p>
                    <p>Status: <span class="status-${order.status}">${order.status}</span></p>
                </div>
                <div class="order-actions">
                    ${order.status === 'pending' ? 
                        `<button class="btn btn-success" data-action="process-order" data-order-id="${order.id}">Process</button>` :
                        `<button class="btn btn-info" data-action="view-order-details" data-order-id="${order.id}">View Details</button>`
                    }
                </div>
            </div>
        `).join('');
    }

    displaySuppliers(suppliers) {
        const container = document.getElementById('suppliers-container');
        if (!container) return;

        container.innerHTML = suppliers.map(supplier => `
            <div class="supplier-card" data-supplier-id="${supplier.id}">
                <div class="supplier-info">
                    <h3>${supplier.name}</h3>
                    <p>Contact: ${supplier.contact_person}</p>
                    <p>Phone: ${supplier.phone}</p>
                    <p>Email: ${supplier.email}</p>
                    <p>Rating: ${this.generateStarRating(supplier.rating)}</p>
                </div>
                <div class="supplier-actions">
                    <button class="btn btn-primary" data-action="edit-supplier" data-supplier-id="${supplier.id}">Edit</button>
                    <button class="btn btn-info" data-action="view-supplier-orders" data-supplier-id="${supplier.id}">View Orders</button>
                </div>
            </div>
        `).join('');
    }

    async handleInventoryUpdate(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'update_inventory', ...data }
            );
            
            this.manager.showNotification('Inventory updated successfully', 'success');
            this.loadInventoryData();
            event.target.reset();
        } catch (error) {
            console.error('Inventory update failed:', error);
        }
    }

    async handleOrderCreate(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'create_order', ...data }
            );
            
            this.manager.showNotification('Order created successfully', 'success');
            this.loadOrders();
            event.target.reset();
        } catch (error) {
            console.error('Order creation failed:', error);
        }
    }

    async handleSupplierUpdate(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'update_supplier', ...data }
            );
            
            this.manager.showNotification('Supplier updated successfully', 'success');
            this.loadSuppliers();
            event.target.reset();
        } catch (error) {
            console.error('Supplier update failed:', error);
        }
    }

    async handleStockUpdate(data) {
        const newStock = prompt('Enter new stock quantity:');
        if (newStock === null || isNaN(newStock)) {
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { 
                    action: 'update_stock',
                    item_id: data.itemId,
                    new_quantity: parseInt(newStock)
                }
            );
            
            this.manager.showNotification('Stock updated successfully', 'success');
            this.loadInventoryData();
        } catch (error) {
            console.error('Stock update failed:', error);
        }
    }

    async handleOrderProcess(orderId) {
        if (!confirm('Are you sure you want to process this order?')) {
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'process_order', order_id: orderId }
            );
            
            this.manager.showNotification('Order processed successfully', 'success');
            this.loadOrders();
            this.loadInventoryData(); // Refresh inventory after processing
        } catch (error) {
            console.error('Order processing failed:', error);
        }
    }

    async handleBarcodeScan() {
        // Simulate barcode scanning
        const barcode = prompt('Enter barcode or scan:');
        if (!barcode) return;

        try {
            const result = await this.manager.makeRequest(
                `${this.apiEndpoint}?action=scan_barcode&barcode=${encodeURIComponent(barcode)}`
            );
            
            this.displayBarcodeResult(result.data);
        } catch (error) {
            console.error('Barcode scan failed:', error);
        }
    }

    async handleGenerateReport(data) {
        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'generate_report', report_type: data.reportType }
            );
            
            // Create download link for report
            const link = document.createElement('a');
            link.href = result.data.report_url;
            link.download = `warehouse_report_${new Date().toISOString().split('T')[0]}.pdf`;
            link.click();
            
            this.manager.showNotification('Report generated successfully', 'success');
        } catch (error) {
            console.error('Report generation failed:', error);
        }
    }

    async searchInventory(query) {
        if (query.length < 2) {
            this.loadInventoryData();
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                `${this.apiEndpoint}?action=search_inventory&query=${encodeURIComponent(query)}`
            );
            this.displayInventory(result.data);
        } catch (error) {
            console.error('Inventory search failed:', error);
        }
    }

    displayBarcodeResult(data) {
        const container = document.getElementById('barcode-result');
        if (!container) return;

        container.innerHTML = `
            <div class="barcode-result">
                <h3>Barcode Scan Result</h3>
                <div class="item-details">
                    <p><strong>Item:</strong> ${data.name}</p>
                    <p><strong>SKU:</strong> ${data.sku}</p>
                    <p><strong>Current Stock:</strong> ${data.current_stock}</p>
                    <p><strong>Location:</strong> ${data.location}</p>
                </div>
            </div>
        `;
    }

    initializeBarcodeScanner() {
        // Initialize barcode scanner if available
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            const scanButton = document.getElementById('barcode-scanner-btn');
            if (scanButton) {
                scanButton.addEventListener('click', () => {
                    this.startBarcodeScanner();
                });
            }
        }
    }

    startBarcodeScanner() {
        // This would integrate with a real barcode scanner library
        this.manager.showNotification('Barcode scanner initialized', 'info');
    }

    startRealTimeUpdates() {
        setInterval(() => {
            this.loadInventoryData();
            this.loadOrders();
        }, 60000); // Update every minute
    }

    // Utility methods
    getStockLevelClass(currentStock, minStock) {
        if (currentStock <= 0) return 'out-of-stock';
        if (currentStock <= minStock) return 'low-stock';
        return 'in-stock';
    }

    generateStarRating(rating) {
        const stars = '★'.repeat(Math.floor(rating)) + '☆'.repeat(5 - Math.floor(rating));
        return stars;
    }

    calculateReorderPoint(currentStock, averageUsage, leadTime) {
        return Math.ceil(averageUsage * leadTime * 1.5);
    }

    formatInventoryStatus(status) {
        const statusMap = {
            'in_stock': 'In Stock',
            'low_stock': 'Low Stock',
            'out_of_stock': 'Out of Stock',
            'discontinued': 'Discontinued'
        };
        return statusMap[status] || status;
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

    // Analytics and reporting
    async generateInventoryReport() {
        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'generate_inventory_report' }
            );
            
            this.displayInventoryReport(result.data);
        } catch (error) {
            console.error('Failed to generate inventory report:', error);
        }
    }

    displayInventoryReport(data) {
        const container = document.getElementById('inventory-report');
        if (!container) return;

        container.innerHTML = `
            <div class="inventory-report">
                <h3>Inventory Report</h3>
                <div class="report-summary">
                    <div class="summary-item">
                        <span>Total Items:</span>
                        <span>${data.total_items}</span>
                    </div>
                    <div class="summary-item">
                        <span>Low Stock Items:</span>
                        <span>${data.low_stock_items}</span>
                    </div>
                    <div class="summary-item">
                        <span>Out of Stock:</span>
                        <span>${data.out_of_stock_items}</span>
                    </div>
                    <div class="summary-item">
                        <span>Total Value:</span>
                        <span>${this.manager.formatCurrency(data.total_value)}</span>
                    </div>
                </div>
            </div>
        `;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WarehouseModule;
}
