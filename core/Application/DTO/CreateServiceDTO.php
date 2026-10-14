<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class CreateServiceDTO {
    public string $workerId;
    public string $title;
    public int $estimatedPrice;
    public string $priceType;
    public ?string $categoryId;
    public ?string $description;

    public function __construct(array $data) {
        $errors = [];

        $this->workerId = trim($data['workerId'] ?? $data['worker_id'] ?? '');
        if (empty($this->workerId)) {
            $errors[] = 'El identificador del trabajador es obligatorio.';
        }

        $this->title = trim($data['title'] ?? '');
        if (strlen($this->title) < 3) {
            $errors[] = 'El título del servicio debe tener al menos 3 caracteres.';
        }

        $this->estimatedPrice = (int)($data['estimatedPrice'] ?? $data['estimated_price'] ?? 0);
        if ($this->estimatedPrice <= 0) {
            $errors[] = 'El precio estimado debe ser mayor a 0.';
        }

        $this->priceType = $data['priceType'] ?? $data['price_type'] ?? 'desde';
        $this->categoryId = $data['categoryId'] ?? $data['category_id'] ?? null;
        $this->description = !empty($data['description']) ? trim($data['description']) : null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
