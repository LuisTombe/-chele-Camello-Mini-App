<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class ChangeRequestStatusDTO {
    public string $requestId;
    public string $targetStatus;
    public string $actorRole;
    public string $actorId;
    public ?string $scheduledDate;
    public ?string $scheduledTime;
    public ?string $reason;

    public function __construct(array $data) {
        $errors = [];

        $this->requestId = trim($data['requestId'] ?? $data['request_id'] ?? '');
        if (empty($this->requestId)) {
            $errors[] = 'El identificador de la solicitud (requestId) es obligatorio.';
        }

        $this->targetStatus = strtoupper(trim($data['status'] ?? $data['targetStatus'] ?? ''));
        $validStatuses = ['PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'PROGRAMADA', 'EN_PROCESO', 'FINALIZADA', 'CANCELADA'];
        if (!in_array($this->targetStatus, $validStatuses, true)) {
            $errors[] = "Estado de destino '{$this->targetStatus}' inválido.";
        }

        $this->actorRole = strtoupper(trim($data['role'] ?? 'CLIENTE'));
        $this->actorId = trim($data['actorId'] ?? $data['userId'] ?? 'actor');
        $this->scheduledDate = $data['scheduledDate'] ?? null;
        $this->scheduledTime = $data['scheduledTime'] ?? null;
        $this->reason = $data['reason'] ?? null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
