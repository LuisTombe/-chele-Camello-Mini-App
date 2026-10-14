-- ===================================================
-- Esquema de Base de Datos MySQL: Échele Camello
-- Optimizado para XAMPP MySQL (Puerto 3306)
-- Charset: UTF8MB4
-- ===================================================

CREATE DATABASE IF NOT EXISTS `echele_camello` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `echele_camello`;

-- 1. Tabla de Categorías Técnicas
CREATE TABLE IF NOT EXISTS `categories` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon_name` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `technician_count` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Trabajadores / Camelladores
CREATE TABLE IF NOT EXISTS `workers` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `phone` VARCHAR(50),
    `avatar` TEXT,
    `specialty` VARCHAR(150) NOT NULL,
    `category_id` VARCHAR(50) NOT NULL,
    `secondary_trades` JSON,
    `bio` TEXT,
    `experience_years` INT DEFAULT 0,
    `sena_certified` TINYINT(1) DEFAULT 0,
    `certificate_title` VARCHAR(255),
    `neighborhood` VARCHAR(100) NOT NULL,
    `distance_km` DECIMAL(4,1) DEFAULT 0.0,
    `rating` DECIMAL(3,2) DEFAULT 5.00,
    `total_reviews` INT DEFAULT 0,
    `completed_jobs` INT DEFAULT 0,
    `min_rate` INT DEFAULT 0,
    `is_available` TINYINT(1) DEFAULT 1,
    `working_hours` VARCHAR(150),
    `portfolio` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_neighborhood` (`neighborhood`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Servicios Ofrecidos por Trabajador
CREATE TABLE IF NOT EXISTS `services` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `worker_id` VARCHAR(50) NOT NULL,
    `category_id` VARCHAR(50),
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `estimated_price` INT NOT NULL,
    `price_type` ENUM('fijo', 'desde', 'por_hora') DEFAULT 'desde',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_worker` (`worker_id`),
    CONSTRAINT `fk_service_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Reseñas y Calificaciones
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `worker_id` VARCHAR(50) NOT NULL,
    `request_id` VARCHAR(50) NULL,
    `client_name` VARCHAR(100) NOT NULL,
    `client_avatar` TEXT,
    `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT,
    `review_date` VARCHAR(50),
    `service_title` VARCHAR(150),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rev_worker` (`worker_id`),
    CONSTRAINT `fk_review_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Solicitudes de Servicio (Camellos)
CREATE TABLE IF NOT EXISTS `service_requests` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `client_id` VARCHAR(50) NOT NULL,
    `client_name` VARCHAR(100) NOT NULL,
    `client_phone` VARCHAR(50),
    `client_avatar` TEXT,
    `worker_id` VARCHAR(50) NOT NULL,
    `worker_name` VARCHAR(100) NOT NULL,
    `worker_specialty` VARCHAR(150),
    `worker_avatar` TEXT,
    `service_title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(100),
    `status` ENUM('PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'PROGRAMADA', 'EN_PROCESO', 'FINALIZADA', 'CANCELADA') DEFAULT 'PENDIENTE',
    `scheduled_date` VARCHAR(50) NOT NULL,
    `scheduled_time` VARCHAR(20) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `neighborhood` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `estimated_cost` INT NOT NULL,
    `urgent` TINYINT(1) DEFAULT 0,
    `diagnosis_photo` TEXT,
    `has_review` TINYINT(1) DEFAULT 0,
    `created_at` VARCHAR(50),
    `updated_at` VARCHAR(50),
    INDEX `idx_req_worker` (`worker_id`),
    INDEX `idx_req_client` (`client_id`),
    INDEX `idx_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de Mensajes del Chat en Tiempo Real
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `request_id` VARCHAR(50) NOT NULL,
    `sender_id` VARCHAR(50) NOT NULL,
    `sender_name` VARCHAR(100) NOT NULL,
    `sender_role` ENUM('CLIENTE', 'TRABAJADOR') NOT NULL,
    `text` TEXT NOT NULL,
    `timestamp` VARCHAR(50) NOT NULL,
    `attachment_url` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chat_request` (`request_id`),
    CONSTRAINT `fk_chat_request` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla de Auditoría del Sistema
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `action` VARCHAR(100) NOT NULL,
    `user` VARCHAR(100) NOT NULL,
    `timestamp` VARCHAR(50) NOT NULL,
    `schema_name` VARCHAR(100) NOT NULL,
    `details` TEXT NOT NULL,
    `severity` ENUM('INFO', 'WARN', 'CRITICAL') DEFAULT 'INFO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla de Usuarios y Autenticación (schema_usuarios)
CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('CLIENTE', 'TRABAJADOR', 'ADMINISTRADOR') DEFAULT 'CLIENTE',
    `phone` VARCHAR(50),
    `avatar` TEXT,
    `is_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabla de Reportes y Moderación (schema_reputacion / moderacion)
CREATE TABLE IF NOT EXISTS `reports` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `request_id` VARCHAR(50) NULL,
    `reporter_id` VARCHAR(50) NOT NULL,
    `reporter_role` ENUM('CLIENTE', 'TRABAJADOR', 'ADMINISTRADOR') NOT NULL,
    `target_type` ENUM('WORKER', 'SERVICE', 'REVIEW', 'SOLICITUD') NOT NULL,
    `target_id` VARCHAR(50) NOT NULL,
    `reason` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('ABIERTO', 'EN_REVISION', 'RESUELTO', 'DESCARTADO') DEFAULT 'ABIERTO',
    `resolution_notes` TEXT NULL,
    `resolved_by` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_report_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

