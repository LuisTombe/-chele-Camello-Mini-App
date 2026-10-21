<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class MarkNotificationReadDTO {
    public ?string $notificationId;
    public string $userId;
    public bool $markAll;

    public function __construct(array $data) {
        $userId = trim($data['userId'] ?? '');
        $notificationId = isset($data['notificationId']) ? trim($data['notificationId']) : null;
        $markAll = !empty($data['markAll']);

        if ($userId === '') {
            throw new ValidationException(['El userId es requerido para marcar notificaciones.']);
        }

        if (!$markAll && !$notificationId) {
            throw new ValidationException(['Se debe especificar notificationId o el indicador markAll.']);
        }

        $this->userId = $userId;
        $this->notificationId = $notificationId;
        $this->markAll = $markAll;
    }
}
