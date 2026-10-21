<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Category;
use PDO;

class CategoryRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM `categories` ORDER BY `name` ASC");
        return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(string $id): ?Category {
        $stmt = $this->pdo->prepare("SELECT * FROM `categories` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Category::fromArray($row) : null;
    }

    public function save(Category $category): void {
        $stmt = $this->pdo->prepare("INSERT INTO `categories` (
            `id`, `name`, `icon_name`, `description`, `technician_count`
        ) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `name` = VALUES(`name`),
            `icon_name` = VALUES(`icon_name`),
            `description` = VALUES(`description`),
            `technician_count` = VALUES(`technician_count`)");

        $stmt->execute([
            $category->getId(),
            $category->getName(),
            $category->getIconName(),
            $category->getDescription(),
            $category->getTechnicianCount()
        ]);
    }

    public function incrementTechnicianCount(string $categoryId): void {
        $stmt = $this->pdo->prepare("UPDATE `categories` SET `technician_count` = `technician_count` + 1 WHERE `id` = ?");
        $stmt->execute([$categoryId]);
    }

    public function decrementTechnicianCount(string $categoryId): void {
        $stmt = $this->pdo->prepare("UPDATE `categories` SET `technician_count` = GREATEST(0, `technician_count` - 1) WHERE `id` = ?");
        $stmt->execute([$categoryId]);
    }
}
