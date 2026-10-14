<?php
namespace App\Infrastructure\Adapter\Storage;

use App\Domain\Exception\ValidationException;

class LocalStorageAdapter implements FileStorageAdapterInterface {
    private string $baseDir;
    private string $publicPrefix;
    private array $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'exe', 'bat', 'cmd', 'sh', 'js', 'py', 'pl'];

    public function __construct(?string $baseDir = null, string $publicPrefix = 'assets/uploads/') {
        $this->baseDir = $baseDir ?? (__DIR__ . '/../../../../assets/uploads/');
        $this->publicPrefix = rtrim($publicPrefix, '/') . '/';

        if (!is_dir($this->baseDir)) {
            @mkdir($this->baseDir, 0755, true);
        }
    }

    public function isValid(array $fileData, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'], int $maxBytes = 5242880): bool {
        if (!isset($fileData['tmp_name']) || empty($fileData['tmp_name']) || !file_exists($fileData['tmp_name'])) {
            return false;
        }

        if (($fileData['size'] ?? 0) > $maxBytes) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);

        return in_array($realMime, $allowedMimes, true);
    }

    public function save(array $fileData, string $subfolder = 'general'): string {
        if (!isset($fileData['tmp_name']) || !file_exists($fileData['tmp_name'])) {
            throw new ValidationException(['No se recibió un archivo válido para almacenar.']);
        }

        // Obtener extensión original
        $originalName = $fileData['name'] ?? 'file';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($ext, $this->dangerousExtensions, true)) {
            throw new ValidationException(["Extensión de archivo '{$ext}' rechazada por seguridad."]);
        }

        // Validar MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($realMime, $allowedMimes, true)) {
            throw new ValidationException(["Formato de archivo MIME '{$realMime}' no permitido."]);
        }

        // Crear subcarpeta segura
        $cleanSub = preg_replace('/[^a-zA-Z0-9_\-]/', '', $subfolder);
        $targetDir = rtrim($this->baseDir, '/') . '/' . $cleanSub . '/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Generar nombre aleatorio seguro (UUID/hash)
        $safeName = bin2hex(random_bytes(16)) . '.' . ($ext ?: 'bin');
        $targetPath = $targetDir . $safeName;

        if (is_uploaded_file($fileData['tmp_name'])) {
            if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                throw new ValidationException(['No se pudo guardar el archivo en el servidor.']);
            }
        } else {
            // Soporte para archivos movidos en pruebas locales
            if (!copy($fileData['tmp_name'], $targetPath)) {
                throw new ValidationException(['No se pudo escribir el archivo en el disco.']);
            }
        }

        // Retornar URL pública relativa (nunca la ruta física del servidor)
        return $this->publicPrefix . $cleanSub . '/' . $safeName;
    }

    public function delete(string $relativeUrl): bool {
        $cleanUrl = str_replace($this->publicPrefix, '', $relativeUrl);
        $filePath = rtrim($this->baseDir, '/') . '/' . ltrim($cleanUrl, '/');

        if (file_exists($filePath) && is_file($filePath)) {
            return @unlink($filePath);
        }
        return false;
    }
}
