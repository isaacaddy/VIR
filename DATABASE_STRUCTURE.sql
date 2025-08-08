-- Database: `dvla_db`
-- Create database
CREATE DATABASE dvla_db;
USE dvla_db;
-- --------------------------------------------------------

-- Table structure for table `activity_logs`
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `activity_logs`
INSERT INTO `activity_logs` (`id`, `action`, `user_email`, `details`, `created_at`) VALUES
(1, 'User Login', 'kingdenzil166@gmail.com', 'Admin user logged in', '2024-11-20 15:59:05'),
(2, 'Record Created', 'kingdenzil13@gmail.com', 'New vehicle record created', '2024-11-21 00:44:28'),
(3, 'Record Updated', 'kingdenzil166@gmail.com', 'Vehicle record updated', '2024-11-21 00:44:50'),
(4, 'Data Export', 'kingdenzil166@gmail.com', 'Data exported to CSV', '2024-11-21 01:15:20'),
(5, 'User Registration', 'kingdenzil513@gmail.com', 'New user account created', '2024-11-21 02:30:15'),
(6, 'Ownership Transfer', 'kingdenzil13@gmail.com', 'Vehicle ownership transferred', '2024-11-21 03:45:10'),
(7, 'Record Deleted', 'kingdenzil166@gmail.com', 'Vehicle record deleted', '2024-11-21 04:20:35'),
(8, 'System Backup', 'kingdenzil166@gmail.com', 'Database backup created', '2024-11-21 05:10:25');

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

-- --------------------------------------------------------

-- Table structure for table `vehicle_registrations`
CREATE TABLE `vehicle_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_number` varchar(20) NOT NULL UNIQUE,
  `chassis_number` varchar(50) NOT NULL UNIQUE,
  `engine_number` varchar(50) NOT NULL,
  `vehicle_make` varchar(100) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `year_of_manufacture` year(4) NOT NULL,
  `body_type` varchar(50) NOT NULL,
  `color` varchar(30) NOT NULL,
  `fuel_type` enum('Petrol','Diesel','Electric','Hybrid') NOT NULL,
  `cubic_capacity` varchar(20) NOT NULL,
  `number_of_cylinders` int(11) NOT NULL,
  `vehicle_use` enum('Private','Commercial','Public') NOT NULL,
  `declaration_number` varchar(50) NOT NULL,
  `owner_full_name` varchar(100) NOT NULL,
  `owner_postal_address` text NOT NULL,
  `owner_residential_address` text NOT NULL,
  `owner_contact` varchar(20) NOT NULL,
  `owner_email` varchar(100) NOT NULL,
  `registration_date` date NOT NULL,
  `certificate_number` varchar(50) NOT NULL UNIQUE,
  `status` enum('Active','Inactive','Suspended','Expired') DEFAULT 'Active',
  `inspector_name` varchar(100) DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_number` (`vehicle_number`),
  KEY `idx_chassis_number` (`chassis_number`),
  KEY `idx_owner_name` (`owner_full_name`),
  KEY `idx_registration_date` (`registration_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `vehicle_registrations`
INSERT INTO `vehicle_registrations` (
  `vehicle_number`, `chassis_number`, `engine_number`, `vehicle_make`, `model_name`, 
  `year_of_manufacture`, `body_type`, `color`, `fuel_type`, `cubic_capacity`, 
  `number_of_cylinders`, `vehicle_use`, `declaration_number`, `owner_full_name`, 
  `owner_postal_address`, `owner_residential_address`, `owner_contact`, `owner_email`, 
  `registration_date`, `certificate_number`, `status`, `inspector_name`, `inspection_date`, `remarks`
) VALUES
('GH-1234-20', 'ABC123456789', 'ENG789456123', 'Toyota', 'Camry', 2020, 'Sedan', 'Blue', 'Petrol', '2500cc', 4, 'Private', 'DECL-2024-001', 'John Doe', 'P.O. Box 123, Accra', '15 Independence Avenue, Accra', '+233-24-123-4567', 'john.doe@email.com', '2024-01-15', 'CERT-2024-001', 'Active', 'Inspector Adams', '2024-01-13', 'Vehicle in excellent condition'),

('GH-5678-21', 'DEF987654321', 'ENG456789012', 'Honda', 'Civic', 2021, 'Sedan', 'White', 'Petrol', '1800cc', 4, 'Private', 'DECL-2024-002', 'Jane Smith', 'P.O. Box 456, Kumasi', '25 Castle Road, Kumasi', '+233-24-987-6543', 'jane.smith@email.com', '2024-02-20', 'CERT-2024-002', 'Active', 'Inspector Brown', '2024-02-18', 'All documents verified'),

('GH-9012-22', 'GHI456789123', 'ENG123456789', 'Nissan', 'Altima', 2022, 'Sedan', 'Black', 'Petrol', '2000cc', 4, 'Commercial', 'DECL-2024-003', 'Michael Johnson', 'P.O. Box 789, Takoradi', '10 Harbor Street, Takoradi', '+233-24-555-0123', 'michael.johnson@email.com', '2024-03-10', 'CERT-2024-003', 'Active', 'Inspector Clark', '2024-03-08', 'Commercial use approved'),

('GH-3456-23', 'JKL789123456', 'ENG987654321', 'Hyundai', 'Elantra', 2023, 'Sedan', 'Silver', 'Petrol', '1600cc', 4, 'Private', 'DECL-2024-004', 'Sarah Wilson', 'P.O. Box 321, Tema', '5 Community Center Road, Tema', '+233-24-777-8888', 'sarah.wilson@email.com', '2024-04-05', 'CERT-2024-004', 'Active', 'Inspector Davis', '2024-04-03', 'New vehicle registration'),

('GH-7890-19', 'MNO321654987', 'ENG654321098', 'Ford', 'Focus', 2019, 'Hatchback', 'Red', 'Petrol', '1500cc', 4, 'Private', 'DECL-2024-005', 'David Brown', 'P.O. Box 654, Cape Coast', '20 University Road, Cape Coast', '+233-24-333-4444', 'david.brown@email.com', '2024-05-12', 'CERT-2024-005', 'Active', 'Inspector Evans', '2024-05-10', 'Regular maintenance required'),

('GH-2468-20', 'PQR654987321', 'ENG321098765', 'Volkswagen', 'Jetta', 2020, 'Sedan', 'Gray', 'Diesel', '2000cc', 4, 'Private', 'DECL-2024-006', 'Lisa Anderson', 'P.O. Box 987, Ho', '15 Volta Street, Ho', '+233-24-666-7777', 'lisa.anderson@email.com', '2024-06-18', 'CERT-2024-006', 'Active', 'Inspector Foster', '2024-06-16', 'Diesel engine inspection passed'),

('GH-1357-21', 'STU987321654', 'ENG098765432', 'Kia', 'Optima', 2021, 'Sedan', 'Blue', 'Petrol', '2400cc', 4, 'Commercial', 'DECL-2024-007', 'Robert Taylor', 'P.O. Box 147, Sunyani', '8 Regional Hospital Road, Sunyani', '+233-24-999-0000', 'robert.taylor@email.com', '2024-07-22', 'CERT-2024-007', 'Active', 'Inspector Green', '2024-07-20', 'Commercial license verified'),

('GH-8024-22', 'VWX321987654', 'ENG765432109', 'Mazda', 'CX-5', 2022, 'SUV', 'White', 'Petrol', '2500cc', 4, 'Private', 'DECL-2024-008', 'Emily Davis', 'P.O. Box 258, Tamale', '12 Northern Regional Road, Tamale', '+233-24-111-2222', 'emily.davis@email.com', '2024-08-15', 'CERT-2024-008', 'Active', 'Inspector Harris', '2024-08-13', 'SUV safety features verified'),

('GH-4680-23', 'YZA654321987', 'ENG432109876', 'Subaru', 'Outback', 2023, 'SUV', 'Green', 'Petrol', '2000cc', 4, 'Private', 'DECL-2024-009', 'Christopher Lee', 'P.O. Box 369, Bolgatanga', '7 Upper East Street, Bolgatanga', '+233-24-444-5555', 'christopher.lee@email.com', '2024-09-08', 'CERT-2024-009', 'Active', 'Inspector Johnson', '2024-09-06', 'All-wheel drive system checked'),

('GH-9753-24', 'BCD987654123', 'ENG109876543', 'Chevrolet', 'Malibu', 2024, 'Sedan', 'Black', 'Hybrid', '1800cc', 4, 'Private', 'DECL-2024-010', 'Amanda White', 'P.O. Box 741, Wa', '3 Upper West Avenue, Wa', '+233-24-888-9999', 'amanda.white@email.com', '2024-10-12', 'CERT-2024-010', 'Active', 'Inspector King', '2024-10-10', 'Hybrid system inspection completed');

-- --------------------------------------------------------

-- Table structure for table `vehicle_history`
CREATE TABLE `vehicle_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `chassis_number` varchar(50) NOT NULL,
  `action_type` enum('registration','ownership_change','update','inspection','renewal') NOT NULL,
  `action_description` text NOT NULL,
  `previous_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `performed_by` varchar(100) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_id` (`vehicle_id`),
  KEY `idx_vehicle_number` (`vehicle_number`),
  KEY `idx_chassis_number` (`chassis_number`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_performed_at` (`performed_at`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle_registrations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `vehicle_history`
INSERT INTO `vehicle_history` (
  `vehicle_id`, `vehicle_number`, `chassis_number`, `action_type`, `action_description`, 
  `previous_data`, `new_data`, `performed_by`, `performed_at`, `remarks`
) VALUES
(1, 'GH-1234-20', 'ABC123456789', 'registration', 'Initial vehicle registration', NULL, 
 '{"vehicle_make": "Toyota", "model_name": "Camry", "year_of_manufacture": "2020", "color": "Blue", "owner_full_name": "John Doe", "owner_contact": "+233-24-123-4567", "owner_email": "john.doe@email.com"}', 
 'Inspector Adams', '2024-01-15 10:30:00', 'Initial registration completed successfully'),

(1, 'GH-1234-20', 'ABC123456789', 'inspection', 'Annual vehicle inspection', NULL,
 '{"inspection_date": "2024-01-13", "inspector_name": "Inspector Adams", "status": "Passed", "remarks": "Vehicle in excellent condition"}',
 'Inspector Adams', '2024-01-13 14:15:00', 'Annual inspection passed'),

(2, 'GH-5678-21', 'DEF987654321', 'registration', 'Initial vehicle registration', NULL,
 '{"vehicle_make": "Honda", "model_name": "Civic", "year_of_manufacture": "2021", "color": "White", "owner_full_name": "Jane Smith", "owner_contact": "+233-24-987-6543", "owner_email": "jane.smith@email.com"}',
 'Inspector Brown', '2024-02-20 09:45:00', 'Registration completed with all documents verified'),

(3, 'GH-9012-22', 'GHI456789123', 'registration', 'Commercial vehicle registration', NULL,
 '{"vehicle_make": "Nissan", "model_name": "Altima", "year_of_manufacture": "2022", "color": "Black", "vehicle_use": "Commercial", "owner_full_name": "Michael Johnson", "owner_contact": "+233-24-555-0123"}',
 'Inspector Clark', '2024-03-10 11:20:00', 'Commercial use license approved'),

(1, 'GH-1234-20', 'ABC123456789', 'update', 'Owner contact information updated', 
 '{"owner_contact": "+233-24-123-4567", "owner_email": "john.doe@email.com"}',
 '{"owner_contact": "+233-24-123-4567", "owner_email": "john.doe@newemail.com"}',
 'John Doe', '2024-06-15 16:30:00', 'Email address updated by owner request'),

(4, 'GH-3456-23', 'JKL789123456', 'registration', 'New vehicle registration', NULL,
 '{"vehicle_make": "Hyundai", "model_name": "Elantra", "year_of_manufacture": "2023", "color": "Silver", "owner_full_name": "Sarah Wilson", "owner_contact": "+233-24-777-8888"}',
 'Inspector Davis', '2024-04-05 13:10:00', 'New vehicle registration processed'),

(5, 'GH-7890-19', 'MNO321654987', 'registration', 'Vehicle registration renewal', NULL,
 '{"vehicle_make": "Ford", "model_name": "Focus", "year_of_manufacture": "2019", "color": "Red", "owner_full_name": "David Brown", "status": "Active"}',
 'Inspector Evans', '2024-05-12 10:45:00', 'Registration renewed for another year');

-- --------------------------------------------------------

-- Table structure for table `inspectors`
CREATE TABLE `inspectors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `employee_id` varchar(20) NOT NULL UNIQUE,
  `contact` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `department` varchar(50) NOT NULL,
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `hire_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `inspectors`
INSERT INTO `inspectors` (`name`, `employee_id`, `contact`, `email`, `department`, `status`, `hire_date`) VALUES
('Inspector Adams', 'INS001', '+233-20-111-1111', 'adams@dvla.gov.gh', 'Vehicle Inspection', 'Active', '2020-01-15'),
('Inspector Brown', 'INS002', '+233-20-222-2222', 'brown@dvla.gov.gh', 'Vehicle Inspection', 'Active', '2020-03-20'),
('Inspector Clark', 'INS003', '+233-20-333-3333', 'clark@dvla.gov.gh', 'Commercial Vehicles', 'Active', '2019-11-10'),
('Inspector Davis', 'INS004', '+233-20-444-4444', 'davis@dvla.gov.gh', 'Vehicle Inspection', 'Active', '2021-02-28'),
('Inspector Evans', 'INS005', '+233-20-555-5555', 'evans@dvla.gov.gh', 'Vehicle Inspection', 'Active', '2020-08-12'),
('Inspector Foster', 'INS006', '+233-20-666-6666', 'foster@dvla.gov.gh', 'Diesel Vehicles', 'Active', '2021-05-18'),
('Inspector Green', 'INS007', '+233-20-777-7777', 'green@dvla.gov.gh', 'Commercial Vehicles', 'Active', '2019-09-25'),
('Inspector Harris', 'INS008', '+233-20-888-8888', 'harris@dvla.gov.gh', 'SUV & Trucks', 'Active', '2020-12-03'),
('Inspector Johnson', 'INS009', '+233-20-999-9999', 'johnson@dvla.gov.gh', 'All-Terrain Vehicles', 'Active', '2021-07-14'),
('Inspector King', 'INS010', '+233-20-101-0101', 'king@dvla.gov.gh', 'Hybrid & Electric', 'Active', '2022-01-20');

-- --------------------------------------------------------

-- Table structure for table `vehicle_types`
CREATE TABLE `vehicle_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `category` enum('Passenger','Commercial','Special') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `vehicle_types`
INSERT INTO `vehicle_types` (`type_name`, `description`, `category`) VALUES
('Sedan', 'Four-door passenger car with separate trunk', 'Passenger'),
('Hatchback', 'Compact car with rear door that opens upward', 'Passenger'),
('SUV', 'Sport Utility Vehicle with higher ground clearance', 'Passenger'),
('Pickup Truck', 'Light truck with open cargo area', 'Commercial'),
('Van', 'Large vehicle for cargo or passenger transport', 'Commercial'),
('Bus', 'Large vehicle for public passenger transport', 'Commercial'),
('Motorcycle', 'Two-wheeled motor vehicle', 'Passenger'),
('Truck', 'Heavy vehicle for cargo transport', 'Commercial'),
('Ambulance', 'Emergency medical transport vehicle', 'Special'),
('Fire Truck', 'Emergency fire fighting vehicle', 'Special');

-- --------------------------------------------------------

-- Table structure for table `registration_statistics`
CREATE TABLE `registration_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `total_registrations` int(11) DEFAULT 0,
  `new_registrations` int(11) DEFAULT 0,
  `renewals` int(11) DEFAULT 0,
  `ownership_transfers` int(11) DEFAULT 0,
  `inspections_completed` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Insert sample data for `registration_statistics`
INSERT INTO `registration_statistics` (`stat_date`, `total_registrations`, `new_registrations`, `renewals`, `ownership_transfers`, `inspections_completed`) VALUES
('2024-01-31', 1250, 45, 12, 8, 65),
('2024-02-29', 1295, 50, 15, 10, 75),
('2024-03-31', 1345, 55, 18, 12, 85),
('2024-04-30', 1400, 60, 20, 15, 95),
('2024-05-31', 1460, 65, 22, 18, 105),
('2024-06-30', 1525, 70, 25, 20, 115),
('2024-07-31', 1595, 75, 28, 22, 125),
('2024-08-31', 1670, 80, 30, 25, 135),
('2024-09-30', 1750, 85, 32, 28, 145),
('2024-10-31', 1835, 90, 35, 30, 155);

COMMIT;