<?php
/**
 * Migración para soporte completo de Patrones de Diseño
 * - Extensión de usuarios (Estado de cuenta, tokens de recuperación)
 * - Extensión de servicios (Estado de publicación)
 * - Tabla de notificaciones (ms-comunicacion / Publish-Subscribe)
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();
    echo "✔ Conectado a base de datos MySQL (Puerto: " . Database::getActivePort() . ")\n";

    // 1. Columnas en users
    $userCols = $pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $userCols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `status` ENUM('ACTIVO', 'SUSPENDIDO', 'BLOQUEADO') DEFAULT 'ACTIVO' AFTER `role`");
        echo "✔ Columna 'status' añadida a tabla 'users'.\n";
    }
    if (!in_array('status_reason', $userCols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `status_reason` TEXT NULL AFTER `status`");
        echo "✔ Columna 'status_reason' añadida a tabla 'users'.\n";
    }
    if (!in_array('reset_token', $userCols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(100) NULL AFTER `verification_token`");
        echo "✔ Columna 'reset_token' añadida a tabla 'users'.\n";
    }
    if (!in_array('reset_expires', $userCols)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `reset_expires` VARCHAR(50) NULL AFTER `reset_token`");
        echo "✔ Columna 'reset_expires' añadida a tabla 'users'.\n";
    }

    // 2. Columna en services
    $svcCols = $pdo->query("SHOW COLUMNS FROM `services`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $svcCols)) {
        $pdo->exec("ALTER TABLE `services` ADD COLUMN `status` ENUM('ACTIVO', 'INACTIVO', 'SUSPENDIDO') DEFAULT 'ACTIVO' AFTER `price_type`");
        $pdo->exec("UPDATE `services` SET `status` = 'INACTIVO' WHERE `is_active` = 0");
        echo "✔ Columna 'status' añadida a tabla 'services'.\n";
    }

    // 3. Tabla notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
        `id` VARCHAR(50) NOT NULL PRIMARY KEY,
        `user_id` VARCHAR(50) NOT NULL,
        `title` VARCHAR(150) NOT NULL,
        `message` TEXT NOT NULL,
        `type` ENUM('SOLICITUD', 'MENSAJE', 'MODERACION', 'SISTEMA') DEFAULT 'SOLICITUD',
        `link` VARCHAR(255) NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_notif_user` (`user_id`),
        INDEX `idx_notif_read` (`is_read`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✔ Tabla 'notifications' verificada/creada exitosamente.\n";

    echo "✔ ¡Migración de base de datos finalizada con éxito!\n";
} catch (Exception $e) {
    echo "✖ Error durante migración: " . $e->getMessage() . "\n";
    exit(1);
}
