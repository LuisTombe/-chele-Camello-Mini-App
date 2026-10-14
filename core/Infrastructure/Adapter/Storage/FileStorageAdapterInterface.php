<?php
namespace App\Infrastructure\Adapter\Storage;

interface FileStorageAdapterInterface {
    /**
     * Guarda un archivo recibido por upload ($_FILES['field'])
     * y retorna la URL pública relativa
     */
    public function save(array $fileData, string $subfolder = 'general'): string;

    /**
     * Elimina un archivo a partir de su URL pública relativa
     */
    public function delete(string $relativeUrl): bool;

    /**
     * Valida MIME type y tamaño
     */
    public function isValid(array $fileData, array $allowedMimes = [], int $maxBytes = 5242880): bool;
}
