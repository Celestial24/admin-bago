-- SQL Schema for Legal Management System
-- Database: legal_management_system

-- Create contracts table for Risk Analysis
CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    case_id VARCHAR(50) NOT NULL,
    risk_level ENUM('Low', 'Medium', 'High') NOT NULL,
    risk_score INT NOT NULL CHECK (risk_score >= 0 AND risk_score <= 100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample contracts data
INSERT INTO contracts (name, case_id, risk_level, risk_score) VALUES
('Hotel Lease Agreement.pdf', 'C-001', 'High', 85),
('Supplier Contract.docx', 'C-002', 'Medium', 60),
('Employment Agreement.pdf', 'C-003', 'Low', 25),
('Service Provider Contract.pdf', 'C-004', 'High', 90);

-- Create contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    role VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample contacts data
INSERT INTO contacts (name, email, phone, role) VALUES
('John Smith', 'john@hotelchain.com', '(555) 123-4567', 'Hotel Manager'),
('Sarah Johnson', 'sarah@restaurant.com', '(555) 987-6543', 'Restaurant Owner'),
('Michael Brown', 'michael@supplier.com', '(555) 456-7890', 'Supplier');
