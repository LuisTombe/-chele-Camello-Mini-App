<?php
/**
 * PSR-4 Autoloader para el namespace App\
 * Mapea App\ hacia __DIR__
 */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Carga de eventos de dominio agrupados
require_once __DIR__ . '/Domain/Event/DomainEvents.php';
