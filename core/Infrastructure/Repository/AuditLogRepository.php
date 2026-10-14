<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\AuditLog;
use PDO;

class AuditLogRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(AuditLog $log): void {
        $stmt = $this->pdo->prepare("INSERT INTO `audit_logs` (
            `id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $log->getId(),
            $log->getAction(),
            $log->getUser(),
            $log->getTimestamp(),
            $log->getSchemaName(),
            $log->getDetails(),
            $log->getSeverity(),
            $log->getCreatedAt()
        ]);
    }

    public function findRecent(int $limit = 30): array {
        $stmt = $this->pdo->prepare("SELECT * FROM `audit_logs` ORDER BY `created_at` DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $logs = [];
        foreach ($rows as $row) {
            $logs[] = AuditLog::fromArray($row);
        }
        return $logs;
    }
}
