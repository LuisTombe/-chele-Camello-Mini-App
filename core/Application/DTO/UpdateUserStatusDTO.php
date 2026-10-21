<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class UpdateUserStatusDTO {
    public string $userId;
    public string $targetStatus;
    public string $actorRole;
    public ?string $reason;

    public function __construct(array $data) {
        $userId = trim($data['userId'] ?? '');
        $targetStatus = strtoupper(trim($data['status'] ?? $data['targetStatus'] ?? ''));
        $actorRole = strtoupper(trim($data['actorRole'] ?? $data['role'] ?? 'ADMINISTRADOR'));
        $reason = trim($data['reason'] ?? 'Acción de moderación administrativa');

        $errors = [];
        if ($userId === '') {
            $errors[] = 'El userId es obligatorio.';
        }
        if (!in_array($targetStatus, ['ACTIVO', 'SUSPENDIDO', 'BLOQUEADO'], true)) {
            $errors[] = "El estado '{$targetStatus}' no es válido. Opciones: ACTIVO, SUSPENDIDO, BLOQUEADO.";
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->userId = $userId;
        $this->targetStatus = $targetStatus;
        $this->actorRole = $actorRole;
        $this->reason = $reason;
    }
}
