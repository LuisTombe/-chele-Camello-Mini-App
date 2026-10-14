<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class CreateReportDTO {
    public ?string $requestId;
    public string $reporterId;
    public string $reporterRole;
    public string $targetType;
    public string $targetId;
    public string $reason;
    public string $description;

    public function __construct(array $data) {
        $errors = [];

        $this->targetType = strtoupper(trim($data['targetType'] ?? $data['target_type'] ?? ''));
        if (!in_array($this->targetType, ['WORKER', 'SERVICE', 'REVIEW', 'SOLICITUD'], true)) {
            $errors[] = "targetType inválido. Tipos permitidos: WORKER, SERVICE, REVIEW, SOLICITUD.";
        }

        $this->targetId = trim($data['targetId'] ?? $data['target_id'] ?? '');
        if (empty($this->targetId)) {
            $errors[] = 'El identificador del recurso reportado (targetId) es obligatorio.';
        }

        $this->reason = trim($data['reason'] ?? '');
        if (strlen($this->reason) < 3) {
            $errors[] = 'El motivo del reporte debe tener al menos 3 caracteres.';
        }

        $this->description = trim($data['description'] ?? '');
        if (strlen($this->description) < 5) {
            $errors[] = 'El detalle de la descripción debe tener al menos 5 caracteres.';
        }

        $this->reporterId = trim($data['reporterId'] ?? $data['reporter_id'] ?? 'actor-me');
        $this->reporterRole = strtoupper(trim($data['reporterRole'] ?? $data['reporter_role'] ?? 'CLIENTE'));
        $this->requestId = !empty($data['requestId']) ? trim($data['requestId']) : (!empty($data['request_id']) ? trim($data['request_id']) : null);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
