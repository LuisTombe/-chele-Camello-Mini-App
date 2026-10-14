<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Report;
use PDO;

class ReportRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Report {
        $stmt = $this->pdo->prepare("SELECT * FROM `reports` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Report::fromArray($row) : null;
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM `reports` WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND `status` = ?";
            $params[] = strtoupper($filters['status']);
        }

        if (!empty($filters['targetType'])) {
            $sql .= " AND `target_type` = ?";
            $params[] = strtoupper($filters['targetType']);
        }

        $sql .= " ORDER BY `created_at` DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reports = [];
        foreach ($rows as $row) {
            $reports[] = Report::fromArray($row);
        }
        return $reports;
    }

    public function save(Report $report): void {
        $stmt = $this->pdo->prepare("INSERT INTO `reports` (
            `id`, `request_id`, `reporter_id`, `reporter_role`, `target_type`, `target_id`,
            `reason`, `description`, `status`, `resolution_notes`, `resolved_by`, `created_at`, `updated_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `status` = VALUES(`status`),
            `resolution_notes` = VALUES(`resolution_notes`),
            `resolved_by` = VALUES(`resolved_by`),
            `updated_at` = VALUES(`updated_at`)");

        $stmt->execute([
            $report->getId(),
            $report->getRequestId(),
            $report->getReporterId(),
            $report->getReporterRole(),
            $report->getTargetType(),
            $report->getTargetId(),
            $report->getReason(),
            $report->getDescription(),
            $report->getStatus(),
            $report->getResolutionNotes(),
            $report->getResolvedBy(),
            $report->getCreatedAt(),
            $report->getUpdatedAt()
        ]);
    }
}
