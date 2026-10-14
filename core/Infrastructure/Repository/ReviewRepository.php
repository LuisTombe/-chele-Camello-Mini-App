<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Review;
use PDO;

class ReviewRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Review $review): void {
        $stmt = $this->pdo->prepare("INSERT INTO `reviews` (
            `id`, `worker_id`, `request_id`, `client_name`, `client_avatar`, `rating`, `comment`, `review_date`, `service_title`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $review->getId(),
            $review->getWorkerId(),
            $review->getRequestId(),
            $review->getClientName(),
            $review->getClientAvatar(),
            $review->getRating(),
            $review->getComment(),
            $review->getReviewDate(),
            $review->getServiceTitle(),
            $review->getCreatedAt()
        ]);
    }

    public function findByWorkerId(string $workerId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM `reviews` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
        $stmt->execute([$workerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = [];
        foreach ($rows as $row) {
            $reviews[] = Review::fromArray($row);
        }
        return $reviews;
    }

    public function hasReviewForRequest(string $requestId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `reviews` WHERE `request_id` = ?");
        $stmt->execute([$requestId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function calculateWorkerAverage(string $workerId): array {
        $stmt = $this->pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM `reviews` WHERE `worker_id` = ?");
        $stmt->execute([$workerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $avg = !empty($row['avg_rating']) ? round((float)$row['avg_rating'], 2) : 5.0;
        $count = (int)($row['total'] ?? 0);

        return [
            'avg' => $avg,
            'count' => $count
        ];
    }
}
