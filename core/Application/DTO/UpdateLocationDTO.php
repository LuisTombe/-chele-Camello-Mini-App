<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class UpdateLocationDTO {
    public string $workerId;
    public string $neighborhood;
    public float $distanceKm;

    public function __construct(array $data) {
        $workerId = trim($data['workerId'] ?? $data['id'] ?? '');
        $neighborhood = trim($data['neighborhood'] ?? '');
        $distanceKm = (float)($data['distanceKm'] ?? $data['distance_km'] ?? 0.0);

        $errors = [];
        if ($workerId === '') {
            $errors[] = 'El workerId es obligatorio.';
        }
        if ($neighborhood === '') {
            $errors[] = 'El barrio de cobertura es obligatorio.';
        }
        if ($distanceKm < 0) {
            $errors[] = 'La distancia no puede ser negativa.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->workerId = $workerId;
        $this->neighborhood = $neighborhood;
        $this->distanceKm = $distanceKm;
    }
}
