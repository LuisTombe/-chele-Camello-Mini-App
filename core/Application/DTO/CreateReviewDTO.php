<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class CreateReviewDTO {
    public string $workerId;
    public ?string $requestId;
    public int $rating;
    public ?string $comment;
    public string $clientName;
    public ?string $clientAvatar;
    public ?string $serviceTitle;

    public function __construct(array $data) {
        $errors = [];

        $this->workerId = trim($data['workerId'] ?? $data['worker_id'] ?? '');
        if (empty($this->workerId)) {
            $errors[] = 'El trabajador a calificar (workerId) es obligatorio.';
        }

        $this->requestId = !empty($data['requestId']) ? trim($data['requestId']) : (!empty($data['request_id']) ? trim($data['request_id']) : null);

        $this->rating = (int)($data['rating'] ?? 5);
        if ($this->rating < 1 || $this->rating > 5) {
            $errors[] = 'La calificación debe estar entre 1 y 5 estrellas.';
        }

        $this->comment = !empty($data['comment']) ? trim($data['comment']) : null;
        $this->clientName = trim($data['clientName'] ?? $data['client_name'] ?? 'Usuario Verificado');
        $this->clientAvatar = $data['clientAvatar'] ?? $data['client_avatar'] ?? null;
        $this->serviceTitle = $data['serviceTitle'] ?? $data['service_title'] ?? null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
