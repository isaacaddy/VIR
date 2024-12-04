-- Database: `dvla_db`
-- Create database
CREATE DATABASE dvla_db;
USE dvla_db;
-- --------------------------------------------------------

-- Table structure for table `activity_logs`
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `user` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `ownership_changes`
CREATE TABLE `ownership_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `co_full_name` varchar(100) NOT NULL,
  `co_postal_address` text DEFAULT NULL,
  `co_residential_address` text DEFAULT NULL,
  `co_contact` varchar(20) NOT NULL,
  `co_email` varchar(100) NOT NULL,
  `co_tin` varchar(50) DEFAULT NULL,
  `po_full_name` varchar(100) NOT NULL,
  `po_postal_address` text DEFAULT NULL,
  `po_residential_address` text DEFAULT NULL,
  `po_contact` varchar(20) NOT NULL,
  `po_email` varchar(100) NOT NULL,
  `po_tin` varchar(50) DEFAULT NULL,
  `transfer_date` date NOT NULL,
  `vehicle_make` varchar(100) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `chassis_number` varchar(50) NOT NULL UNIQUE,
  `year_of_manufacture` year(4) NOT NULL,
  `body_type` varchar(50) NOT NULL,
  `color` varchar(30) NOT NULL,
  `vehicle_use` enum('Commercial','Private','Public') NOT NULL,
  `fuel_type` enum('Petrol','Diesel','Electric','Hybrid') NOT NULL,
  `cubic_capacity` int(11) NOT NULL,
  `engine_number` varchar(50) NOT NULL,
  `number_of_cylinders` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `status` enum('active','inactive','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chassis` (`chassis_number`),
  KEY `idx_co_email` (`co_email`),
  KEY `idx_co_name` (`co_full_name`),
  KEY `idx_transfer_date` (`transfer_date`),
  KEY `idx_vehicle_number` (`vehicle_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `transfers`
CREATE TABLE `transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_owner_name` varchar(100) NOT NULL,
  `new_owner_email` varchar(100) DEFAULT NULL,
  `new_owner_contact` varchar(20) DEFAULT NULL,
  `new_owner_address` text DEFAULT NULL,
  `new_owner_tin` varchar(50) DEFAULT NULL,
  `previous_owner_name` varchar(100) NOT NULL,
  `previous_owner_contact` varchar(20) DEFAULT NULL,
  `previous_owner_email` varchar(100) DEFAULT NULL,
  `previous_owner_address` text DEFAULT NULL,
  `previous_owner_tin` varchar(50) DEFAULT NULL,
  `vehicle_make` varchar(100) NOT NULL,
  `chassis_number` varchar(50) NOT NULL UNIQUE,
  `body_type` varchar(50) DEFAULT NULL,
  `vehicle_use` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(30) DEFAULT NULL,
  `year_of_manufacture` year(4) DEFAULT NULL,
  `cubic_capacity` int(11) DEFAULT NULL,
  `engine_number` varchar(50) DEFAULT NULL,
  `number_of_cylinders` int(11) DEFAULT NULL,
  `transfer_date` date NOT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `transfer_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transfer_date` (`transfer_date`),
  KEY `idx_status` (`status`),
  KEY `idx_chassis` (`chassis_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `users`
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'drewstan', 'kingdenzil166@gmail.com', '$2y$10$j3mOI/EpaI7IZN3NVDew5uCjTJ9F5mYZus8JTnIXHU.3OWo5jpyVS', 'admin', '2024-11-20 15:59:05'),
(2, 'Isaac Addy', 'kingdenzil13@gmail.com', '$2y$10$/hSGiqVC277VRC0SbXRkd.33oTBiEBouxQWHzdbnSROG3cB0Dau/y', 'member', '2024-11-21 00:44:28'),
(4, 'Isaac Addy', 'kingdenzil513@gmail.com', '$2y$10$8ZxudgCsLLyQ4qjsBprRUe5I7/kkO08lEuuuXh.Z0LFhrR950Pfjy', 'admin', '2024-11-21 00:44:50');

COMMIT;