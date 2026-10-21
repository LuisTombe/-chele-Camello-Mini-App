<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class ResetPasswordDTO {
    public string $token;
    public string $newPassword;

    public function __construct(array $data) {
        $token = trim($data['token'] ?? '');
        $password = $data['password'] ?? $data['newPassword'] ?? '';

        $errors = [];
        if ($token === '') {
            $errors[] = 'El token de recuperación es requerido.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->token = $token;
        $this->newPassword = $password;
    }
}
