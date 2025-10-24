/**
 * HR Module - JavaScript Integration
 * Handles employee self-service and HR operations
 */

class HRModule {
    constructor(integrationManager) {
        this.manager = integrationManager;
        this.apiEndpoint = integrationManager.apiEndpoints.hrSelfService;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadEmployeeData();
        this.loadLeaveRequests();
        this.loadPayrollData();
        this.initializeProfileForm();
    }

    setupEventListeners() {
        // Leave request form
        const leaveForm = document.getElementById('leave-request-form');
        if (leaveForm) {
            leaveForm.addEventListener('submit', (e) => this.handleLeaveRequest(e));
        }

        // Profile update form
        const profileForm = document.getElementById('profile-update-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => this.handleProfileUpdate(e));
        }

        // Payroll inquiry
        const payrollForm = document.getElementById('payroll-inquiry-form');
        if (payrollForm) {
            payrollForm.addEventListener('submit', (e) => this.handlePayrollInquiry(e));
        }

        // Document upload
        const documentForm = document.getElementById('document-upload-form');
        if (documentForm) {
            documentForm.addEventListener('submit', (e) => this.handleDocumentUpload(e));
        }

        // Action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="cancel-leave"]')) {
                this.handleCancelLeave(e.target.dataset.leaveId);
            }
            if (e.target.matches('[data-action="download-payslip"]')) {
                this.handleDownloadPayslip(e.target.dataset.payslipId);
            }
            if (e.target.matches('[data-action="view-attendance"]')) {
                this.handleViewAttendance(e.target.dataset.employeeId);
            }
        });
    }

    async loadEmployeeData() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_employee_profile`);
            this.displayEmployeeProfile(result.data);
        } catch (error) {
            console.error('Failed to load employee data:', error);
        }
    }

    async loadLeaveRequests() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_leave_requests`);
            this.displayLeaveRequests(result.data);
        } catch (error) {
            console.error('Failed to load leave requests:', error);
        }
    }

    async loadPayrollData() {
        try {
            const result = await this.manager.makeRequest(`${this.apiEndpoint}?action=get_payroll_data`);
            this.displayPayrollData(result.data);
        } catch (error) {
            console.error('Failed to load payroll data:', error);
        }
    }

    displayEmployeeProfile(employee) {
        const container = document.getElementById('employee-profile');
        if (!container) return;

        container.innerHTML = `
            <div class="profile-card">
                <div class="profile-header">
                    <img src="${employee.photo || '/images/default-avatar.png'}" alt="Profile Photo" class="profile-photo">
                    <div class="profile-info">
                        <h2>${employee.name}</h2>
                        <p>${employee.position}</p>
                        <p>Employee ID: ${employee.employee_id}</p>
                        <p>Department: ${employee.department}</p>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="detail-section">
                        <h3>Contact Information</h3>
                        <p>Email: ${employee.email}</p>
                        <p>Phone: ${employee.phone}</p>
                        <p>Address: ${employee.address}</p>
                    </div>
                    <div class="detail-section">
                        <h3>Employment Details</h3>
                        <p>Hire Date: ${this.manager.formatDate(employee.hire_date)}</p>
                        <p>Manager: ${employee.manager_name}</p>
                        <p>Status: <span class="status-${employee.status}">${employee.status}</span></p>
                    </div>
                </div>
            </div>
        `;
    }

    displayLeaveRequests(requests) {
        const container = document.getElementById('leave-requests-container');
        if (!container) return;

        container.innerHTML = requests.map(request => `
            <div class="leave-request-card" data-leave-id="${request.id}">
                <div class="request-info">
                    <h3>${request.leave_type}</h3>
                    <p>Start Date: ${this.manager.formatDate(request.start_date)}</p>
                    <p>End Date: ${this.manager.formatDate(request.end_date)}</p>
                    <p>Duration: ${request.duration} days</p>
                    <p>Reason: ${request.reason}</p>
                    <p>Status: <span class="status-${request.status}">${request.status}</span></p>
                </div>
                <div class="request-actions">
                    ${request.status === 'pending' ? 
                        `<button class="btn btn-warning" data-action="cancel-leave" data-leave-id="${request.id}">Cancel</button>` :
                        ''
                    }
                    <button class="btn btn-info" data-action="view-leave-details" data-leave-id="${request.id}">View Details</button>
                </div>
            </div>
        `).join('');
    }

    displayPayrollData(payroll) {
        const container = document.getElementById('payroll-container');
        if (!container) return;

        container.innerHTML = `
            <div class="payroll-summary">
                <h3>Current Pay Period</h3>
                <div class="payroll-details">
                    <div class="payroll-item">
                        <span>Basic Salary:</span>
                        <span>${this.manager.formatCurrency(payroll.basic_salary)}</span>
                    </div>
                    <div class="payroll-item">
                        <span>Overtime:</span>
                        <span>${this.manager.formatCurrency(payroll.overtime)}</span>
                    </div>
                    <div class="payroll-item">
                        <span>Allowances:</span>
                        <span>${this.manager.formatCurrency(payroll.allowances)}</span>
                    </div>
                    <div class="payroll-item">
                        <span>Deductions:</span>
                        <span>-${this.manager.formatCurrency(payroll.deductions)}</span>
                    </div>
                    <div class="payroll-item total">
                        <span>Net Pay:</span>
                        <span>${this.manager.formatCurrency(payroll.net_pay)}</span>
                    </div>
                </div>
                <div class="payslip-actions">
                    <button class="btn btn-primary" data-action="download-payslip" data-payslip-id="${payroll.payslip_id}">Download Payslip</button>
                </div>
            </div>
        `;
    }

    async handleLeaveRequest(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'submit_leave_request', ...data }
            );
            
            this.manager.showNotification('Leave request submitted successfully', 'success');
            this.loadLeaveRequests();
            event.target.reset();
        } catch (error) {
            console.error('Leave request failed:', error);
        }
    }

    async handleProfileUpdate(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'PUT',
                { action: 'update_profile', ...data }
            );
            
            this.manager.showNotification('Profile updated successfully', 'success');
            this.loadEmployeeData();
        } catch (error) {
            console.error('Profile update failed:', error);
        }
    }

    async handlePayrollInquiry(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'payroll_inquiry', ...data }
            );
            
            this.displayPayrollInquiry(result.data);
        } catch (error) {
            console.error('Payroll inquiry failed:', error);
        }
    }

    async handleDocumentUpload(event) {
        event.preventDefault();
        const formData = new FormData(event.target);

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'upload_document', formData: formData }
            );
            
            this.manager.showNotification('Document uploaded successfully', 'success');
            event.target.reset();
        } catch (error) {
            console.error('Document upload failed:', error);
        }
    }

    async handleCancelLeave(leaveId) {
        if (!confirm('Are you sure you want to cancel this leave request?')) {
            return;
        }

        try {
            const result = await this.manager.makeRequest(
                this.apiEndpoint,
                'POST',
                { action: 'cancel_leave_request', leave_id: leaveId }
            );
            
            this.manager.showNotification('Leave request cancelled', 'success');
            this.loadLeaveRequests();
        } catch (error) {
            console.error('Leave cancellation failed:', error);
        }
    }

    async handleDownloadPayslip(payslipId) {
        try {
            const result = await this.manager.makeRequest(
                `${this.apiEndpoint}?action=download_payslip&payslip_id=${payslipId}`
            );
            
            // Create download link
            const link = document.createElement('a');
            link.href = result.data.download_url;
            link.download = `payslip_${payslipId}.pdf`;
            link.click();
            
            this.manager.showNotification('Payslip downloaded', 'success');
        } catch (error) {
            console.error('Payslip download failed:', error);
        }
    }

    async handleViewAttendance(employeeId) {
        try {
            const result = await this.manager.makeRequest(
                `${this.apiEndpoint}?action=get_attendance&employee_id=${employeeId}`
            );
            
            this.displayAttendanceData(result.data);
        } catch (error) {
            console.error('Failed to load attendance data:', error);
        }
    }

    displayAttendanceData(attendance) {
        const container = document.getElementById('attendance-container');
        if (!container) return;

        container.innerHTML = `
            <div class="attendance-summary">
                <h3>Attendance Summary</h3>
                <div class="attendance-stats">
                    <div class="stat-item">
                        <span>Total Days:</span>
                        <span>${attendance.total_days}</span>
                    </div>
                    <div class="stat-item">
                        <span>Present:</span>
                        <span>${attendance.present_days}</span>
                    </div>
                    <div class="stat-item">
                        <span>Absent:</span>
                        <span>${attendance.absent_days}</span>
                    </div>
                    <div class="stat-item">
                        <span>Late:</span>
                        <span>${attendance.late_days}</span>
                    </div>
                </div>
            </div>
        `;
    }

    displayPayrollInquiry(data) {
        const container = document.getElementById('payroll-inquiry-results');
        if (!container) return;

        container.innerHTML = `
            <div class="inquiry-results">
                <h3>Payroll Inquiry Results</h3>
                <div class="results-content">
                    ${data.map(period => `
                        <div class="payroll-period">
                            <h4>${period.period}</h4>
                            <p>Net Pay: ${this.manager.formatCurrency(period.net_pay)}</p>
                            <p>Status: ${period.status}</p>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    initializeProfileForm() {
        const profileForm = document.getElementById('profile-update-form');
        if (profileForm) {
            // Pre-populate form with current data
            this.loadEmployeeData().then(() => {
                // Form will be populated by displayEmployeeProfile
            });
        }
    }

    // Utility methods
    calculateLeaveBalance(leaveType, usedDays, totalDays) {
        return totalDays - usedDays;
    }

    formatLeaveStatus(status) {
        const statusMap = {
            'pending': 'Pending Approval',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'cancelled': 'Cancelled'
        };
        return statusMap[status] || status;
    }

    validateLeaveRequest(data) {
        const errors = [];
        
        if (!data.start_date || !data.end_date) {
            errors.push('Start and end dates are required');
        }
        
        if (new Date(data.start_date) >= new Date(data.end_date)) {
            errors.push('End date must be after start date');
        }
        
        if (!data.reason || data.reason.length < 10) {
            errors.push('Reason must be at least 10 characters');
        }
        
        return errors;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HRModule;
}
