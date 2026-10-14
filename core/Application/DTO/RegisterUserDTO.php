<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class RegisterUserDTO {
    public string $name;
    public string $email;
    public string $password;
    public string $role;
    public ?string $phone;
    public ?string $avatar;

    public function __construct(array $data) {
        $errors = [];

        $this->name = trim($data['name'] ?? '');
        if (strlen($this->name) < 2) {
            $errors[] = 'El nombre es obligatorio y debe tener al menos 2 caracteres.';
        }

        $this->email = trim(strtolower($data['email'] ?? ''));
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        $this->password = $data['password'] ?? '';
        if (strlen($this->password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        // REGLA ARQUITECTÓNICA: El registro público NO permite auto-asignarse rol ADMINISTRADOR
        $requestedRole = strtoupper(trim($data['role'] ?? 'CLIENTE'));
        if ($requestedRole === 'ADMINISTRADOR') {
            $errors[] = 'No está permitido registrarse directamente con el rol ADMINISTRADOR.';
        } elseif (!in_array($requestedRole, ['CLIENTE', 'TRABAJADOR'], true)) {
            $this->role = 'CLIENTE';
        } else {
            $this->role = $requestedRole;
        }

        $this->phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $this->avatar = !empty($data['avatar']) ? trim($data['avatar']) : null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
