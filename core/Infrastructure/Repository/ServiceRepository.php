<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Service;
use PDO;

class ServiceRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Service {
        $stmt = $this->pdo->prepare("SELECT * FROM `services` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Service::fromArray($row) : null;
    }

    public function findByWorkerId(string $workerId, bool $onlyActive = false): array {
        $sql = "SELECT * FROM `services` WHERE `worker_id` = ?";
        if ($onlyActive) {
            $sql .= " AND `is_active` = 1 AND `status` = 'ACTIVO'";
        }
        $sql .= " ORDER BY `created_at` DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$workerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $services = [];
        foreach ($rows as $row) {
            $services[] = Service::fromArray($row);
        }
        return $services;
    }

    public function save(Service $service): void {
        $stmt = $this->pdo->prepare("INSERT INTO `services` (
            `id`, `worker_id`, `category_id`, `title`, `description`, `estimated_price`, `price_type`, `status`, `is_active`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `category_id` = VALUES(`category_id`),
            `title` = VALUES(`title`),
            `description` = VALUES(`description`),
            `estimated_price` = VALUES(`estimated_price`),
            `price_type` = VALUES(`price_type`),
            `status` = VALUES(`status`),
            `is_active` = VALUES(`is_active`)");

        $stmt->execute([
            $service->getId(),
            $service->getWorkerId(),
            $service->getCategoryId(),
            $service->getTitle(),
            $service->getDescription(),
            $service->getEstimatedPrice(),
            $service->getPriceType(),
            $service->getStatus(),
            $service->isActive() ? 1 : 0,
            $service->getCreatedAt()
        ]);
    }

    public function updateStatus(string $serviceId, string $status, bool $isActive): void {
        $stmt = $this->pdo->prepare("UPDATE `services` SET `status` = ?, `is_active` = ? WHERE `id` = ?");
        $stmt->execute([$status, $isActive ? 1 : 0, $serviceId]);
    }
}
