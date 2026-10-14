<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Worker;
use PDO;

class WorkerRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Worker {
        $stmt = $this->pdo->prepare("SELECT * FROM `workers` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Worker::fromArray($row) : null;
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM `workers` WHERE 1=1";
        $params = [];

        if (!empty($filters['categoryId'])) {
            $sql .= " AND `category_id` = ?";
            $params[] = $filters['categoryId'];
        }

        if (!empty($filters['neighborhood']) && strtolower($filters['neighborhood']) !== 'todos') {
            $sql .= " AND `neighborhood` = ?";
            $params[] = $filters['neighborhood'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (`name` LIKE ? OR `specialty` LIKE ? OR `neighborhood` LIKE ?)";
            $term = "%" . trim($filters['search']) . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (isset($filters['isAvailable'])) {
            $sql .= " AND `is_available` = ?";
            $params[] = $filters['isAvailable'] ? 1 : 0;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = Worker::fromArray($row);
        }
        return $result;
    }

    public function updateAvailability(string $workerId, bool $available): void {
        $stmt = $this->pdo->prepare("UPDATE `workers` SET `is_available` = ? WHERE `id` = ?");
        $stmt->execute([$available ? 1 : 0, $workerId]);
    }

    public function updateVerification(string $workerId, bool $certified, ?string $title = null): void {
        $stmt = $this->pdo->prepare("UPDATE `workers` SET `sena_certified` = ?, `certificate_title` = ? WHERE `id` = ?");
        $stmt->execute([$certified ? 1 : 0, $title, $workerId]);
    }

    public function updateRating(string $workerId, float $rating, int $totalReviews): void {
        $stmt = $this->pdo->prepare("UPDATE `workers` SET `rating` = ?, `total_reviews` = ? WHERE `id` = ?");
        $stmt->execute([$rating, $totalReviews, $workerId]);
    }

    public function incrementCompletedJobs(string $workerId): void {
        $stmt = $this->pdo->prepare("UPDATE `workers` SET `completed_jobs` = `completed_jobs` + 1 WHERE `id` = ?");
        $stmt->execute([$workerId]);
    }
}
