<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class PasswordRecoveryDTO {
    public string $email;

    public function __construct(array $data) {
        $email = trim($data['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['El correo electrónico para recuperación no es válido.']);
        }

        $this->email = strtolower($email);
    }
}
