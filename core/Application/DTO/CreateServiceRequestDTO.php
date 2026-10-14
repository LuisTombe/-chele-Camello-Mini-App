<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class CreateServiceRequestDTO {
    public string $clientId;
    public string $clientName;
    public ?string $clientPhone;
    public ?string $clientAvatar;
    public string $workerId;
    public string $workerName;
    public ?string $workerSpecialty;
    public ?string $workerAvatar;
    public string $serviceTitle;
    public ?string $category;
    public string $scheduledDate;
    public string $scheduledTime;
    public string $address;
    public string $neighborhood;
    public string $description;
    public int $estimatedCost;
    public bool $urgent;
    public ?string $diagnosisPhoto;

    public function __construct(array $data) {
        $errors = [];

        $this->workerId = trim($data['workerId'] ?? $data['worker_id'] ?? '');
        if (empty($this->workerId)) {
            $errors[] = 'El trabajador destinatario es requerido.';
        }

        $this->workerName = trim($data['workerName'] ?? $data['worker_name'] ?? '');
        $this->workerSpecialty = $data['workerSpecialty'] ?? $data['worker_specialty'] ?? null;
        $this->workerAvatar = $data['workerAvatar'] ?? $data['worker_avatar'] ?? null;

        $this->serviceTitle = trim($data['serviceTitle'] ?? $data['service_title'] ?? 'Servicio Técnico');
        if (empty($this->serviceTitle)) {
            $errors[] = 'El título del servicio es requerido.';
        }

        $this->clientId = trim($data['clientId'] ?? $data['client_id'] ?? 'cli-me');
        $this->clientName = trim($data['clientName'] ?? $data['client_name'] ?? 'Tú (Cliente)');
        $this->clientPhone = $data['clientPhone'] ?? $data['client_phone'] ?? null;
        $this->clientAvatar = $data['clientAvatar'] ?? $data['client_avatar'] ?? null;

        $this->category = $data['category'] ?? 'General';

        // Validación de fecha: formato YYYY-MM-DD y rechazo de fechas pasadas
        $this->scheduledDate = trim($data['scheduledDate'] ?? $data['scheduled_date'] ?? date('Y-m-d'));
        $today = date('Y-m-d');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->scheduledDate)) {
            if ($this->scheduledDate < $today) {
                $errors[] = "No se puede programar una solicitud para una fecha pasada ({$this->scheduledDate}).";
            }
        }

        $this->scheduledTime = trim($data['scheduledTime'] ?? $data['scheduled_time'] ?? date('H:i'));

        $this->address = trim($data['address'] ?? '');
        if (empty($this->address)) {
            $errors[] = 'La dirección del servicio es requerida.';
        }

        $this->neighborhood = trim($data['neighborhood'] ?? 'Neiva');
        $this->description = trim($data['description'] ?? '');
        if (strlen($this->description) < 5) {
            $errors[] = 'La descripción del servicio debe tener al menos 5 caracteres.';
        }

        $this->estimatedCost = (int)($data['estimatedCost'] ?? $data['estimated_cost'] ?? 40000);
        if ($this->estimatedCost <= 0) {
            $errors[] = 'El costo estimado debe ser un valor positivo mayor a 0.';
        }

        $this->urgent = !empty($data['urgent']);
        $this->diagnosisPhoto = $data['diagnosisPhoto'] ?? $data['diagnosis_photo'] ?? null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
