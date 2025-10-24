-- Add login_tb table to admin database
-- This table is required for the login system to work

USE `admin`;

-- Create login_tb table for authentication
CREATE TABLE IF NOT EXISTS `login_tb` (
  `pk` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `account_type` enum('admin','user','manager') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`pk`),
  KEY `idx_username` (`username`),
  KEY `idx_account_type` (`account_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
-- Password is 'admin123' (hashed with password_hash())
INSERT INTO `login_tb` (`name`, `username`, `password`, `account_type`) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('System User', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Note: The password hash above is for 'admin123'
-- To create new password hashes, use PHP: password_hash('your_password', PASSWORD_DEFAULT)
