<?php
/**
 * Bootstrap de Arquitectura y Contenedor de Dependencias
 */
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Infrastructure\Container\AppContainer;

function getAppContainer(): AppContainer {
    static $container = null;
    if ($container === null) {
        $pdo = Database::getConnection();
        $container = new AppContainer($pdo);
    }
    return $container;
}
