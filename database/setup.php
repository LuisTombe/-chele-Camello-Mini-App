<?php
/**
 * Échele Camello - Inicializador y Migrador de Base de Datos
 * Ejecutable vía CLI: php database/setup.php
 * O vía navegador: http://localhost/echele-camello/database/setup.php
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');

$isCli = (php_sapi_name() === 'cli');

function logMsg(string $msg, bool $isCli, string $type = 'info'): void {
    if ($isCli) {
        $prefix = $type === 'success' ? '✔ ' : ($type === 'error' ? '✖ ' : 'ℹ ');
        echo $prefix . strip_tags($msg) . PHP_EOL;
    } else {
        $colors = [
            'info' => 'background:#e0f2fe; color:#0369a1; border-color:#bae6fd;',
            'success' => 'background:#dcfce7; color:#15803d; border-color:#bbf7d0;',
            'error' => 'background:#fee2e2; color:#b91c1c; border-color:#fecaca;',
        ];
        $style = $colors[$type] ?? $colors['info'];
        echo "<div style='padding: 10px 16px; margin-bottom: 8px; border-radius: 8px; border: 1px solid; font-family: monospace; font-size: 13px; {$style}'>{$msg}</div>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><title>Setup BD - Échele Camello</title><style>body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #fbf9f6; color: #12263f; } h1 { color: #d96528; } .card { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }</style></head><body><div class='card'>";
    echo "<h1>🛠️ Échele Camello — Instalador de Base de Datos MySQL</h1>";
}

try {
    logMsg("Iniciando conexión con MySQL en XAMPP...", $isCli, 'info');
    $pdo = Database::getConnection();
    $activePort = Database::getActivePort();
    logMsg("¡Conexión establecida exitosamente con MySQL en el puerto <strong>{$activePort}</strong>!", $isCli, 'success');

    // 1. Ejecutar Schema
    logMsg("Cargando esquema de tablas (schema.sql)...", $isCli, 'info');
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    if (!$schemaSql) {
        throw new Exception("No se pudo leer el archivo schema.sql");
    }
    
    // Separar por sentencias para ejecución limpia
    $pdo->exec($schemaSql);
    logMsg("Esquema creado o verificado correctamente.", $isCli, 'success');

    // 2. Verificar si existen datos, si no ejecutar seed
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM workers");
    $workersCount = (int)$stmt->fetchColumn();

    if ($workersCount === 0 || isset($_GET['force_seed'])) {
        logMsg("Poblando base de datos con datos semilla de Neiva (seed.sql)...", $isCli, 'info');
        $seedSql = file_get_contents(__DIR__ . '/seed.sql');
        if (!$seedSql) {
            throw new Exception("No se pudo leer el archivo seed.sql");
        }
        $pdo->exec($seedSql);
        logMsg("Datos iniciales insertados con éxito.", $isCli, 'success');
    } else {
        logMsg("La base de datos ya contiene {$workersCount} trabajadores registrados. (Usa ?force_seed=1 si deseas reiniciar datos).", $isCli, 'info');
    }

    // 3. Resumen de tablas
    $tables = ['categories', 'workers', 'services', 'reviews', 'service_requests', 'chat_messages', 'audit_logs'];
    logMsg("<strong>Resumen de registros en base de datos:</strong>", $isCli, 'info');
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        logMsg(" • Tabla <code>{$t}</code>: {$count} registros", $isCli, 'info');
    }

    logMsg("<strong>¡Configuración completada al 100%! El sistema está listo para operar con MySQL XAMPP.</strong>", $isCli, 'success');

    if (!$isCli) {
        echo "<div style='margin-top: 24px;'><a href='../index.php' style='display: inline-block; background: #d96528; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold;'>Ir a la Aplicación Principal &rarr;</a></div>";
    }

} catch (Exception $e) {
    logMsg("ERROR al configurar base de datos: " . $e->getMessage(), $isCli, 'error');
}

if (!$isCli) {
    echo "</div></body></html>";
}
