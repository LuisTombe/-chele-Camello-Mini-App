<?php
namespace App\Domain\Entity;

use App\Domain\State\Report\ReportStateInterface;
use App\Domain\State\Report\OpenReportState;
use App\Domain\State\Report\UnderReviewReportState;
use App\Domain\State\Report\ResolvedReportState;
use App\Domain\State\Report\DismissedReportState;

class Report {
    private string $id;
    private ?string $requestId;
    private string $reporterId;
    private string $reporterRole;
    private string $targetType;
    private string $targetId;
    private string $reason;
    private string $description;
    private ReportStateInterface $state;
    private ?string $resolutionNotes;
    private ?string $resolvedBy;
    private string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $reporterId,
        string $reporterRole,
        string $targetType,
        string $targetId,
        string $reason,
        string $description,
        ?string $requestId = null,
        ?ReportStateInterface $state = null,
        ?string $resolutionNotes = null,
        ?string $resolvedBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->reporterId = $reporterId;
        $this->reporterRole = $reporterRole;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->reason = $reason;
        $this->description = $description;
        $this->requestId = $requestId;
        $this->state = $state ?? new OpenReportState();
        $this->resolutionNotes = $resolutionNotes;
        $this->resolvedBy = $resolvedBy;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public static function stateFromStatus(string $status): ReportStateInterface {
        return match ($status) {
            'EN_REVISION' => new UnderReviewReportState(),
            'RESUELTO' => new ResolvedReportState(),
            'DESCARTADO' => new DismissedReportState(),
            default => new OpenReportState(),
        };
    }

    public static function fromArray(array $data): self {
        $statusStr = $data['status'] ?? 'ABIERTO';
        $state = self::stateFromStatus($statusStr);

        return new self(
            $data['id'],
            $data['reporter_id'],
            $data['reporter_role'],
            $data['target_type'],
            $data['target_id'],
            $data['reason'],
            $data['description'],
            $data['request_id'] ?? null,
            $state,
            $data['resolution_notes'] ?? null,
            $data['resolved_by'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    public function transitionTo(ReportStateInterface $newState): void {
        $this->state = $newState;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function startReview(string $adminUser): void {
        $this->state->startReview($this, $adminUser);
    }

    public function resolve(string $adminUser, string $resolutionNotes): void {
        $this->state->resolve($this, $adminUser, $resolutionNotes);
    }

    public function dismiss(string $adminUser, string $reason): void {
        $this->state->dismiss($this, $adminUser, $reason);
    }

    public function setResolutionNotes(?string $notes): void {
        $this->resolutionNotes = $notes;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setResolvedBy(?string $user): void {
        $this->resolvedBy = $user;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getRequestId(): ?string { return $this->requestId; }
    public function getReporterId(): string { return $this->reporterId; }
    public function getReporterRole(): string { return $this->reporterRole; }
    public function getTargetType(): string { return $this->targetType; }
    public function getTargetId(): string { return $this->targetId; }
    public function getReason(): string { return $this->reason; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->state->getStatus(); }
    public function getResolutionNotes(): ?string { return $this->resolutionNotes; }
    public function getResolvedBy(): ?string { return $this->resolvedBy; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'request_id' => $this->requestId,
            'reporter_id' => $this->reporterId,
            'reporter_role' => $this->reporterRole,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->getStatus(),
            'resolution_notes' => $this->resolutionNotes,
            'resolved_by' => $this->resolvedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
