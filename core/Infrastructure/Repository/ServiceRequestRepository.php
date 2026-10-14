<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\ServiceRequest;
use PDO;

class ServiceRequestRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?ServiceRequest {
        $stmt = $this->pdo->prepare("SELECT * FROM `service_requests` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ServiceRequest::fromArray($row) : null;
    }

    public function findByCode(string $code): ?ServiceRequest {
        $stmt = $this->pdo->prepare("SELECT * FROM `service_requests` WHERE `code` = ? LIMIT 1");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ServiceRequest::fromArray($row) : null;
    }

    public function findByFilters(array $filters = []): array {
        $sql = "SELECT * FROM `service_requests` WHERE 1=1";
        $params = [];

        if (!empty($filters['workerId'])) {
            $sql .= " AND `worker_id` = ?";
            $params[] = $filters['workerId'];
        }

        if (!empty($filters['clientId'])) {
            $sql .= " AND `client_id` = ?";
            $params[] = $filters['clientId'];
        }

        if (!empty($filters['status']) && strtoupper($filters['status']) !== 'TODAS') {
            $sql .= " AND `status` = ?";
            $params[] = strtoupper($filters['status']);
        }

        $sql .= " ORDER BY `id` DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $requests = [];
        foreach ($rows as $row) {
            $requests[] = ServiceRequest::fromArray($row);
        }
        return $requests;
    }

    public function save(ServiceRequest $request): void {
        $stmt = $this->pdo->prepare("INSERT INTO `service_requests` (
            `id`, `code`, `client_id`, `client_name`, `client_phone`, `client_avatar`,
            `worker_id`, `worker_name`, `worker_specialty`, `worker_avatar`,
            `service_title`, `category`, `status`, `scheduled_date`, `scheduled_time`,
            `address`, `neighborhood`, `description`, `estimated_cost`, `urgent`,
            `diagnosis_photo`, `has_review`, `created_at`, `updated_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `status` = VALUES(`status`),
            `scheduled_date` = VALUES(`scheduled_date`),
            `scheduled_time` = VALUES(`scheduled_time`),
            `has_review` = VALUES(`has_review`),
            `updated_at` = VALUES(`updated_at`)");

        $stmt->execute([
            $request->getId(),
            $request->getCode(),
            $request->getClientId(),
            $request->getClientName(),
            $request->getClientPhone(),
            $request->getClientAvatar(),
            $request->getWorkerId(),
            $request->getWorkerName(),
            $request->getWorkerSpecialty(),
            $request->getWorkerAvatar(),
            $request->getServiceTitle(),
            $request->getCategory(),
            $request->getStatus(),
            $request->getScheduledDate(),
            $request->getScheduledTime(),
            $request->getAddress(),
            $request->getNeighborhood(),
            $request->getDescription(),
            $request->getEstimatedCost(),
            $request->isUrgent() ? 1 : 0,
            $request->getDiagnosisPhoto(),
            $request->hasReview() ? 1 : 0,
            $request->getCreatedAt(),
            $request->getUpdatedAt()
        ]);
    }

    public function updateStatusAndSchedule(ServiceRequest $request): void {
        $stmt = $this->pdo->prepare("UPDATE `service_requests` SET
            `status` = ?,
            `scheduled_date` = ?,
            `scheduled_time` = ?,
            `has_review` = ?,
            `updated_at` = ?
            WHERE `id` = ?");

        $stmt->execute([
            $request->getStatus(),
            $request->getScheduledDate(),
            $request->getScheduledTime(),
            $request->hasReview() ? 1 : 0,
            $request->getUpdatedAt(),
            $request->getId()
        ]);
    }

    /**
     * Comprueba si el trabajador ya tiene un servicio PROGRAMADO o EN_PROCESO en la misma fecha y hora
     */
    public function isWorkerAvailableAt(string $workerId, string $date, string $time, ?string $excludeRequestId = null): bool {
        $sql = "SELECT COUNT(*) FROM `service_requests`
                WHERE `worker_id` = ?
                  AND `scheduled_date` = ?
                  AND `scheduled_time` = ?
                  AND `status` IN ('PROGRAMADA', 'EN_PROCESO')";
        $params = [$workerId, $date, $time];

        if ($excludeRequestId) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeRequestId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $count = (int)$stmt->fetchColumn();

        return $count === 0;
    }
}
