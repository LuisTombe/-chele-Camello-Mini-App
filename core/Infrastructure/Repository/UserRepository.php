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

    public function save(User $user): void {
        $stmt = $this->pdo->prepare("INSERT INTO `users` (
            `id`, `name`, `email`, `password_hash`, `role`, `phone`, `avatar`, `is_verified`, `verification_token`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `name` = VALUES(`name`),
            `password_hash` = VALUES(`password_hash`),
            `role` = VALUES(`role`),
            `phone` = VALUES(`phone`),
            `avatar` = VALUES(`avatar`),
            `is_verified` = VALUES(`is_verified`),
            `verification_token` = VALUES(`verification_token`)");

        $stmt->execute([
            $user->getId(),
            $user->getName(),
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getPhone(),
            $user->getAvatar(),
            $user->isVerified() ? 1 : 0,
            $user->getVerificationToken(),
            $user->getCreatedAt()
        ]);
    }

    public function verifyAccount(string $userId): void {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `is_verified` = 1, `verification_token` = NULL WHERE `id` = ?");
        $stmt->execute([$userId]);
    }
}
