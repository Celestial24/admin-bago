# Integration Modules

This folder contains the integration modules for the Admin System. Each module is organized by category and provides API endpoints for specific functionality.

## Folder Structure

```
integration/
├── api/                    # Base API classes and common functionality
├── config/                 # Configuration files (database, etc.)
├── hr2/                    # HR2 modules (Employee Self-Service)
├── hr3/                    # HR3 modules (Time & Attendance, Shift Management)
├── log1/                   # LOG1 modules (Warehousing, Asset Management, Document Tracking)
├── core1/                  # Core1 modules (Front Desk, Reservations, Guest Management)
└── README.md              # This file
```

## Modules Overview

### HR2 - Employee Self-Service
- **Employee Self-Service (HR2)**: Employee portal for personal information management
- API: `hr2/employee-self-service-api.php`

### HR3 - Time and Attendance Management
- **Time and Attendance (HR3)**: Time tracking and attendance management
- **Shift and Schedule Management (HR3)**: Shift assignment and schedule management

### LOG1 - Logistics and Operations
- **Smart Warehousing System (LOG1)**: Intelligent warehouse management and inventory tracking
- **Asset Lifecycle & Maintenance (LOG1)**: Asset tracking, maintenance scheduling, and lifecycle management
- **Document Tracking & Logistics Records (LOG1)**: Document management and logistics record tracking
- API: `log1/smart-warehousing-api.php`

### Core1 - Core Business Operations
- **Front desk & Reception Module (Core 1)**: Front desk operations and reception management
- **Reservation & Booking Module (Core 1)**: Reservation and booking management system
- **Guest Relationship Management (Core 1)**: Guest relationship and customer management
- API: `core1/front-desk-api.php`

## API Usage

### Base API Class
All integration APIs extend the `BaseAPI` class which provides:
- Authentication and session management
- Common response formatting
- Permission validation
- Input sanitization
- Error handling

### API Endpoints

#### Menu API
- **GET** `/api/menu-api.php` - Get all menu items for the current user
- **POST** `/api/menu-api.php` - Get specific menu item by ID

#### Employee Self-Service API
- **GET** `/integration/hr2/employee-self-service-api.php` - Get employee data
- **POST** `/integration/hr2/employee-self-service-api.php` - Update employee data
- **PUT** `/integration/hr2/employee-self-service-api.php` - Update employee profile

#### Smart Warehousing API
- **GET** `/integration/log1/smart-warehousing-api.php` - Get warehouse inventory
- **POST** `/integration/log1/smart-warehousing-api.php` - Add inventory item
- **PUT** `/integration/log1/smart-warehousing-api.php` - Update inventory item
- **DELETE** `/integration/log1/smart-warehousing-api.php` - Delete inventory item

#### Front Desk API
- **GET** `/integration/core1/front-desk-api.php?action=guests` - Get guests
- **GET** `/integration/core1/front-desk-api.php?action=reservations` - Get reservations
- **GET** `/integration/core1/front-desk-api.php?action=checkins` - Get check-ins
- **POST** `/integration/core1/front-desk-api.php` - Add guest
- **PUT** `/integration/core1/front-desk-api.php` - Update guest
- **DELETE** `/integration/core1/front-desk-api.php` - Delete guest

## Database Configuration

The database configuration is managed in `config/database.php` using a singleton pattern for connection management.

## Authentication

All APIs require user authentication through session management. Users must be logged in to access any API endpoints.

## Permissions

Different modules have different permission requirements:
- **Employee**: Can access Employee Self-Service and basic Core1 modules
- **Manager**: Can access all modules except Admin-only functions
- **Admin**: Full access to all modules and administrative functions

## Response Format

All APIs return JSON responses in the following format:

### Success Response
```json
{
    "status": "success",
    "message": "Operation completed successfully",
    "data": {...},
    "timestamp": "2025-01-24 11:06:00"
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "timestamp": "2025-01-24 11:06:00"
}
```

## Development Guidelines

1. All new APIs should extend the `BaseAPI` class
2. Use proper error handling and validation
3. Sanitize all input data
4. Follow the established response format
5. Implement proper permission checks
6. Use prepared statements for database queries
7. Log errors appropriately

## Integration with Main System

The integration modules are designed to work seamlessly with the main Admin System:
- Shared session management
- Consistent database connection
- Unified permission system
- Integrated sidebar navigation
