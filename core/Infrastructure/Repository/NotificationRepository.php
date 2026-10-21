<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Notification;
use PDO;

class NotificationRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Notification $notification): void {
        $stmt = $this->pdo->prepare("INSERT INTO `notifications` (
            `id`, `user_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `title` = VALUES(`title`),
            `message` = VALUES(`message`),
            `is_read` = VALUES(`is_read`)");

        $stmt->execute([
            $notification->getId(),
            $notification->getUserId(),
            $notification->getTitle(),
            $notification->getMessage(),
            $notification->getType(),
            $notification->getLink(),
            $notification->isRead() ? 1 : 0,
            $notification->getCreatedAt()
        ]);
    }

    public function findByUserId(string $userId, bool $unreadOnly = false): array {
        $sql = "SELECT * FROM `notifications` WHERE `user_id` = ?";
        if ($unreadOnly) {
            $sql .= " AND `is_read` = 0";
        }
        $sql .= " ORDER BY `created_at` DESC LIMIT 50";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => Notification::fromArray($row), $rows);
    }

    public function markAsRead(string $notificationId, string $userId): void {
        $stmt = $this->pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = ? AND `user_id` = ?");
        $stmt->execute([$notificationId, $userId]);
    }

    public function markAllAsRead(string $userId): void {
        $stmt = $this->pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `user_id` = ?");
        $stmt->execute([$userId]);
    }

    public function getUnreadCount(string $userId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `notifications` WHERE `user_id` = ? AND `is_read` = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
