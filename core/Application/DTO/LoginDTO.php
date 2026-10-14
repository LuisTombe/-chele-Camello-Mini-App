<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class LoginDTO {
    public string $email;
    public string $password;

    public function __construct(array $data) {
        $errors = [];

        $this->email = trim(strtolower($data['email'] ?? ''));
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo electrónico no válido.';
        }

        $this->password = $data['password'] ?? '';
        if (empty($this->password)) {
            $errors[] = 'La contraseña es requerida.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
