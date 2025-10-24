-- capstoneadmin.sql
-- SQL for creating capstoneadmin database and required tables for Facilities Reservation System

CREATE DATABASE IF NOT EXISTS `capstoneadmin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `capstoneadmin`;

-- Table: facilities
CREATE TABLE IF NOT EXISTS `facilities` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(100) DEFAULT 'general',
  `capacity` INT DEFAULT 0,
  `location` VARCHAR(255) DEFAULT '',
  `description` TEXT,
  `hourly_rate` DECIMAL(10,2) DEFAULT 0.00,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `amenities` TEXT DEFAULT NULL,
  `status` ENUM('active','maintenance','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reservations
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `facility_id` INT NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `event_type` VARCHAR(255) DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `guests_count` INT DEFAULT 0,
  `special_requirements` TEXT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_facility` (`facility_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_reservations_facility` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional sample data for facilities
INSERT INTO `facilities` (name, type, capacity, location, description, hourly_rate, image_url, amenities)
VALUES
('Grand Ballroom','banquet',300,'Main Building, 1st Floor','Elegant ballroom perfect for weddings and large events',500.00,'ballroom.jpg','Stage, Sound System, Lighting, Projector'),
('Executive Boardroom','meeting',25,'Business Center, 2nd Floor','Professional meeting space with advanced technology',75.00,'boardroom.jpg','Projector, Video Conferencing, Whiteboard'),
('Poolside Terrace','outdoor',150,'Pool Area','Beautiful outdoor space for parties and receptions',200.00,'poolside.jpg','Sound System, Lighting, Bar Setup'),
('Garden Pavilion','outdoor',100,'Hotel Garden','Charming garden setting for intimate events',150.00,'garden.jpg','Gazebo, Lighting, Garden Furniture'),
('Conference Hall A','conference',120,'Conference Center','Modern conference facility with theater seating',250.00,'conferenceroom.png','Projector, Sound System, Stage'),
('Private Dining Room','dining',40,'Restaurant Wing','Exclusive dining area for private events',100.00,'dining.jpg','Dedicated Service, Audio System'),
('Rooftop Lounge','lounge',80,'Hotel Rooftop','Stylish rooftop venue with panoramic views',300.00,'rooftop.jpg','Bar, Lounge Furniture, Sound System');

-- Example reservation (optional, commented out)
-- INSERT INTO `reservations` (facility_id, customer_name, customer_email, customer_phone, event_type, event_date, start_time, end_time, guests_count, special_requirements, total_amount, status)
-- VALUES (1, 'John Doe','john@example.com','09171234567','Wedding','2025-10-20','10:00:00','14:00:00',150,'Stage and catering',2000.00,'confirmed');

-- End of file