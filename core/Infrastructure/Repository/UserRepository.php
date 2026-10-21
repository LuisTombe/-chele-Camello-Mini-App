<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use PDO;

class UserRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? User::fromArray($row) : null;
    }

    public function findById(string $id): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? User::fromArray($row) : null;
    }

    public function findByVerificationToken(string $token): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `verification_token` = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? User::fromArray($row) : null;
    }

    public function findByResetToken(string $token): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `reset_token` = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? User::fromArray($row) : null;
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM `users` WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND `role` = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY `created_at` DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(fn($row) => User::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function save(User $user): void {
        $stmt = $this->pdo->prepare("INSERT INTO `users` (
            `id`, `name`, `email`, `password_hash`, `role`, `status`, `status_reason`, `phone`, `avatar`, `is_verified`, `verification_token`, `reset_token`, `reset_expires`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `name` = VALUES(`name`),
            `password_hash` = VALUES(`password_hash`),
            `role` = VALUES(`role`),
            `status` = VALUES(`status`),
            `status_reason` = VALUES(`status_reason`),
            `phone` = VALUES(`phone`),
            `avatar` = VALUES(`avatar`),
            `is_verified` = VALUES(`is_verified`),
            `verification_token` = VALUES(`verification_token`),
            `reset_token` = VALUES(`reset_token`),
            `reset_expires` = VALUES(`reset_expires`)");

        $stmt->execute([
            $user->getId(),
            $user->getName(),
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getStatus(),
            $user->getStatusReason(),
            $user->getPhone(),
            $user->getAvatar(),
            $user->isVerified() ? 1 : 0,
            $user->getVerificationToken(),
            $user->getResetToken(),
            $user->getResetExpires(),
            $user->getCreatedAt()
        ]);
    }

    public function verifyAccount(string $userId): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `is_verified` = 1, `verification_token` = NULL WHERE `id` = ?");
        $stmt->execute([$userId]);
    }

    public function updateStatus(string $userId, string $status, ?string $reason): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `status` = ?, `status_reason` = ? WHERE `id` = ?");
        $stmt->execute([$status, $reason, $userId]);
    }

    public function updateRole(string $userId, string $role): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `role` = ? WHERE `id` = ?");
        $stmt->execute([$role, $userId]);
    }

    public function updateResetToken(string $userId, ?string $token, ?string $expires): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `reset_token` = ?, `reset_expires` = ? WHERE `id` = ?");
        $stmt->execute([$token, $expires, $userId]);
    }

    public function updatePassword(string $userId, string $passwordHash): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `password_hash` = ?, `reset_token` = NULL, `reset_expires` = NULL WHERE `id` = ?");
        $stmt->execute([$passwordHash, $userId]);
    }
}
