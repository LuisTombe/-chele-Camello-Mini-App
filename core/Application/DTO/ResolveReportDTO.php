<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class ResolveReportDTO {
    public string $reportId;
    public string $decision;
    public string $resolutionNotes;
    public string $adminUser;

    public function __construct(array $data) {
        $errors = [];

        $this->reportId = trim($data['reportId'] ?? $data['report_id'] ?? '');
        if (empty($this->reportId)) {
            $errors[] = 'El identificador del reporte es obligatorio.';
        }

        $this->decision = strtoupper(trim($data['decision'] ?? 'RESUELTO'));
        if (!in_array($this->decision, ['RESUELTO', 'DESCARTADO', 'EN_REVISION'], true)) {
            $errors[] = "Decisión inválida '{$this->decision}'. Opciones: RESUELTO, DESCARTADO, EN_REVISION.";
        }

        $this->resolutionNotes = trim($data['resolutionNotes'] ?? $data['resolution_notes'] ?? '');
        if (empty($this->resolutionNotes) && $this->decision !== 'EN_REVISION') {
            $errors[] = 'Las notas o motivo de la resolución son obligatorias.';
        }

        $this->adminUser = trim($data['adminUser'] ?? $data['admin_user'] ?? 'admin_moderador');

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
