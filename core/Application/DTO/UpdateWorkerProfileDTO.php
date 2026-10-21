<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class UpdateWorkerProfileDTO {
    public string $workerId;
    public string $name;
    public ?string $phone;
    public ?string $email;
    public string $specialty;
    public string $bio;
    public int $experienceYears;
    public int $minRate;
    public string $workingHours;
    public array $secondaryTrades;

    public function __construct(array $data) {
        $workerId = trim($data['workerId'] ?? $data['id'] ?? '');
        $name = trim($data['name'] ?? '');
        $specialty = trim($data['specialty'] ?? '');

        $errors = [];
        if ($workerId === '') {
            $errors[] = 'El workerId es requerido.';
        }
        if ($name === '') {
            $errors[] = 'El nombre profesional no puede estar vacío.';
        }
        if ($specialty === '') {
            $errors[] = 'La especialidad técnica es requerida.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->workerId = $workerId;
        $this->name = $name;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->specialty = $specialty;
        $this->bio = trim($data['bio'] ?? '');
        $this->experienceYears = max(0, (int)($data['experienceYears'] ?? $data['experience_years'] ?? 0));
        $this->minRate = max(0, (int)($data['minRate'] ?? $data['min_rate'] ?? 0));
        $this->workingHours = trim($data['workingHours'] ?? $data['working_hours'] ?? 'Lun - Sáb: 7:00 AM - 6:00 PM');
        $this->secondaryTrades = is_array($data['secondaryTrades'] ?? null) 
            ? $data['secondaryTrades'] 
            : (json_decode($data['secondary_trades'] ?? '[]', true) ?: []);
    }
}
