<?php
/**
 * Échele Camello - Configuración de Base de Datos MySQL (XAMPP)
 * Conexión PDO con soporte para puerto 3306 (configurable vía DB_PORT o .env)
 */

// Cargar variables desde archivo .env si existe
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!isset($_SERVER[$key]) && !isset($_ENV[$key])) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: 3306);
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'echele_camello');

class Database {
    private static ?PDO $instance = null;
    private static int $activePort = 3306;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = DB_HOST;
        $primaryPort = (int)DB_PORT;
        $user = DB_USER;
        $pass = DB_PASS;
        $dbname = DB_NAME;

        // Intentar conectar al puerto primario (3306)
        $portsToTry = [$primaryPort];
        // Si el puerto primario es 3306, incluir 3308 como alternativa inteligente en caso de que XAMPP esté en 3308
        if ($primaryPort === 3306) {
            $portsToTry[] = 3308;
        } elseif ($primaryPort === 3308) {
            $portsToTry[] = 3306;
        }

        $lastException = null;

        foreach ($portsToTry as $port) {
            try {
                // Primero verificar servidor y crear la BD si no existe
                $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
                $pdoServer = new PDO($dsnServer, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 2
                ]);
                
                $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Conectar directamente a la BD
                $dsnDb = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                self::$instance = new PDO($dsnDb, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                self::$activePort = $port;
                return self::$instance;
            } catch (Exception $e) {
                $lastException = $e;
                continue;
            }
        }

        throw new Exception("No se pudo conectar a MySQL en los puertos [" . implode(', ', $portsToTry) . "]: " . ($lastException ? $lastException->getMessage() : 'Desconocido'));
    }

    public static function getActivePort(): int {
        return self::$activePort;
    }
}
