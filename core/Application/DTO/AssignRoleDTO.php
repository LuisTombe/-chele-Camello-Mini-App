<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class AssignRoleDTO {
    public string $userId;
    public string $newRole;
    public string $actorRole;

    public function __construct(array $data) {
        $userId = trim($data['userId'] ?? '');
        $newRole = strtoupper(trim($data['newRole'] ?? $data['role'] ?? ''));
        $actorRole = strtoupper(trim($data['actorRole'] ?? 'ADMINISTRADOR'));

        $errors = [];
        if ($userId === '') {
            $errors[] = 'El userId es obligatorio.';
        }
        if (!in_array($newRole, ['CLIENTE', 'TRABAJADOR', 'ADMINISTRADOR'], true)) {
            $errors[] = "El rol '{$newRole}' no es válido.";
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->userId = $userId;
        $this->newRole = $newRole;
        $this->actorRole = $actorRole;
    }
}
