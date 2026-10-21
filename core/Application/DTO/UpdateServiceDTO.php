<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class UpdateServiceDTO {
    public string $serviceId;
    public string $title;
    public int $estimatedPrice;
    public string $priceType;
    public ?string $description;

    public function __construct(array $data) {
        $serviceId = trim($data['serviceId'] ?? $data['id'] ?? '');
        $title = trim($data['title'] ?? '');
        $estimatedPrice = (int)($data['estimatedPrice'] ?? $data['estimated_price'] ?? 0);
        $priceType = strtolower(trim($data['priceType'] ?? $data['price_type'] ?? 'desde'));

        $errors = [];
        if ($serviceId === '') {
            $errors[] = 'El serviceId es obligatorio.';
        }
        if ($title === '') {
            $errors[] = 'El título del servicio no puede estar vacío.';
        }
        if ($estimatedPrice <= 0) {
            $errors[] = 'El precio estimado debe ser mayor a 0.';
        }
        if (!in_array($priceType, ['fijo', 'desde', 'por_hora'], true)) {
            $errors[] = 'El tipo de tarifa debe ser: fijo, desde o por_hora.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->serviceId = $serviceId;
        $this->title = $title;
        $this->estimatedPrice = $estimatedPrice;
        $this->priceType = $priceType;
        $this->description = isset($data['description']) ? trim($data['description']) : null;
    }
}
