<?php
namespace App\Domain\Entity;

class AuditLog {
    private string $id;
    private string $action;
    private string $user;
    private string $timestamp;
    private string $schemaName;
    private string $details;
    private string $severity;
    private string $createdAt;

    public function __construct(
        string $id,
        string $action,
        string $user,
        string $timestamp,
        string $schemaName,
        string $details,
        string $severity = 'INFO',
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->action = $action;
        $this->user = $user;
        $this->timestamp = $timestamp;
        $this->schemaName = $schemaName;
        $this->details = $details;
        $this->severity = $severity;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['action'],
            $data['user'],
            $data['timestamp'],
            $data['schema_name'],
            $data['details'],
            $data['severity'] ?? 'INFO',
            $data['created_at'] ?? null
        );
    }

    public function getId(): string { return $this->id; }
    public function getAction(): string { return $this->action; }
    public function getUser(): string { return $this->user; }
    public function getTimestamp(): string { return $this->timestamp; }
    public function getSchemaName(): string { return $this->schemaName; }
    public function getDetails(): string { return $this->details; }
    public function getSeverity(): string { return $this->severity; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'user' => $this->user,
            'timestamp' => $this->timestamp,
            'schema_name' => $this->schemaName,
            'details' => $this->details,
            'severity' => $this->severity,
            'created_at' => $this->createdAt,
        ];
    }
}
