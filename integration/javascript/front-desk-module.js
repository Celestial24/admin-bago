/**
 * Front Desk Module - JavaScript Integration
 * Handles front desk operations and guest management
 */

class FrontDeskModule {
    constructor(integrationManager) {
        this.manager = integrationManager;
        this.apiEndpoint = integrationManager.apiEndpoints.frontDesk;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadGuestData();
        this.loadReservations();
        this.initializeGuestSearch();
    }

    setupEventListeners() {
        // Guest check-in form
        const checkinForm = document.getElementById('guest-checkin-form');
        if (checkinForm) {
            checkinForm.addEventListener('submit', (e) => this.handleCheckinForm(e));
        }

        // Reservation management
        const reservationForm = document.getElementById('reservation-form');
        if (reservationForm) {
            reservationForm.addEventListener('submit', (e) => this.handleReservationForm(e));
        }

        // Guest search
        const searchInput = document.getElementById('guest-search');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => this.searchGuests(e.target.value), 300));
        }

        // Room assignment
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="assign-room"]')) {
                this.handleRoomAssignment(e.target.dataset);
            }
            if (e.target.matches('[data-action="checkout-guest"]')) {
                this.handleCheckout(e.target.dataset.guestId);
            }
        });
    }

    async loadGuestData() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_guests`);
            this.displayGuests(result.data);
        } catch (error) {
            console.error('Failed to load guest data:', error);
        }
    }

    async loadReservations() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_reservations`);
            this.displayReservations(result.data);
        } catch (error) {
            console.error('Failed to load reservations:', error);
        }
    }

    displayGuests(guests) {
        const container = document.getElementById('guests-container');
        if (!container) return;

        container.innerHTML = guests.map(guest => `
            <div class="guest-card" data-guest-id="${guest.id}">
                <div class="guest-info">
                    <h3>${guest.name}</h3>
                    <p>Room: ${guest.room_number || 'Not Assigned'}</p>
                    <p>Check-in: ${this.manager.formatDate(guest.checkin_date)}</p>
                    <p>Status: <span class="status-${guest.status}">${guest.status}</span></p>
                </div>
                <div class="guest-actions">
                    ${guest.status === 'checked_in' ? 
                        `<button class="btn btn-warning" data-action="checkout-guest" data-guest-id="${guest.id}">Checkout</button>` :
                        `<button class="btn btn-success" data-action="checkin-guest" data-guest-id="${guest.id}">Check-in</button>`
                    }
                    <button class="btn btn-info" data-action="view-guest-details" data-guest-id="${guest.id}">View Details</button>
                </div>
            </div>
        `).join('');
    }

    displayReservations(reservations) {
        const container = document.getElementById('reservations-container');
        if (!container) return;

        container.innerHTML = reservations.map(reservation => `
            <div class="reservation-card" data-reservation-id="${reservation.id}">
                <div class="reservation-info">
                    <h3>${reservation.guest_name}</h3>
                    <p>Room Type: ${reservation.room_type}</p>
                    <p>Check-in: ${this.manager.formatDate(reservation.checkin_date)}</p>
                    <p>Check-out: ${this.manager.formatDate(reservation.checkout_date)}</p>
                    <p>Status: <span class="status-${reservation.status}">${reservation.status}</span></p>
                </div>
                <div class="reservation-actions">
                    ${reservation.status === 'confirmed' ? 
                        `<button class="btn btn-success" data-action="process-reservation" data-reservation-id="${reservation.id}">Process</button>` :
                        `<button class="btn btn-info" data-action="view-reservation" data-reservation-id="${reservation.id}">View</button>`
                    }
                </div>
            </div>
        `).join('');
    }

    async handleCheckinForm(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'checkin_guest', ...data }
            );
            
            this.manager.showNotification('Guest checked in successfully', 'success');
            this.loadGuestData();
            event.target.reset();
        } catch (error) {
            console.error('Check-in failed:', error);
        }
    }

    async handleReservationForm(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'create_reservation', ...data }
            );
            
            this.manager.showNotification('Reservation created successfully', 'success');
            this.loadReservations();
            event.target.reset();
        } catch (error) {
            console.error('Reservation creation failed:', error);
        }
    }

    async handleRoomAssignment(data) {
        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { 
                    action: 'assign_room',
                    guest_id: data.guestId,
                    room_number: data.roomNumber
                }
            );
            
            this.manager.showNotification('Room assigned successfully', 'success');
            this.loadGuestData();
        } catch (error) {
            console.error('Room assignment failed:', error);
        }
    }

    async handleCheckout(guestId) {
        if (!confirm('Are you sure you want to checkout this guest?')) {
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'checkout_guest', guest_id: guestId }
            );
            
            this.manager.showNotification('Guest checked out successfully', 'success');
            this.loadGuestData();
        } catch (error) {
            console.error('Checkout failed:', error);
        }
    }

    async searchGuests(query) {
        if (query.length < 2) {
            this.loadGuestData();
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                `${this.apiEndpoint}?action=search_guests&query=${encodeURIComponent(query)}`
            );
            this.displayGuests(result.data);
        } catch (error) {
            console.error('Guest search failed:', error);
        }
    }

    initializeGuestSearch() {
        const searchInput = document.getElementById('guest-search');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.searchGuests(e.target.value);
            }, 300));
        }
    }

    // Real-time updates
    startRealTimeUpdates() {
        setInterval(() => {
            this.loadGuestData();
            this.loadReservations();
        }, 30000); // Update every 30 seconds
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

    formatGuestStatus(status) {
        const statusMap = {
            'checked_in': 'Checked In',
            'checked_out': 'Checked Out',
            'pending': 'Pending',
            'cancelled': 'Cancelled'
        };
        return statusMap[status] || status;
    }

    calculateStayDuration(checkinDate, checkoutDate) {
        const checkin = new Date(checkinDate);
        const checkout = new Date(checkoutDate);
        const diffTime = Math.abs(checkout - checkin);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FrontDeskModule;
}
