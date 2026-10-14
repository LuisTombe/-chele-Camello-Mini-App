<?php
namespace App\Domain\Entity;

use App\Domain\State\Request\RequestStateInterface;
use App\Domain\State\Request\PendingState;
use App\Domain\State\Request\AcceptedState;
use App\Domain\State\Request\RejectedState;
use App\Domain\State\Request\ScheduledState;
use App\Domain\State\Request\InProgressState;
use App\Domain\State\Request\FinishedState;
use App\Domain\State\Request\CancelledState;

class ServiceRequest {
    private string $id;
    private string $code;
    private string $clientId;
    private string $clientName;
    private ?string $clientPhone;
    private ?string $clientAvatar;
    private string $workerId;
    private string $workerName;
    private ?string $workerSpecialty;
    private ?string $workerAvatar;
    private string $serviceTitle;
    private ?string $category;
    private RequestStateInterface $state;
    private string $scheduledDate;
    private string $scheduledTime;
    private string $address;
    private string $neighborhood;
    private string $description;
    private int $estimatedCost;
    private bool $urgent;
    private ?string $diagnosisPhoto;
    private bool $hasReview;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        string $id,
        string $code,
        string $clientId,
        string $clientName,
        string $workerId,
        string $workerName,
        string $serviceTitle,
        string $scheduledDate,
        string $scheduledTime,
        string $address,
        string $neighborhood,
        string $description,
        int $estimatedCost,
        bool $urgent = false,
        ?string $category = null,
        ?string $clientPhone = null,
        ?string $clientAvatar = null,
        ?string $workerSpecialty = null,
        ?string $workerAvatar = null,
        ?string $diagnosisPhoto = null,
        bool $hasReview = false,
        ?RequestStateInterface $initialState = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->clientId = $clientId;
        $this->clientName = $clientName;
        $this->workerId = $workerId;
        $this->workerName = $workerName;
        $this->serviceTitle = $serviceTitle;
        $this->scheduledDate = $scheduledDate;
        $this->scheduledTime = $scheduledTime;
        $this->address = $address;
        $this->neighborhood = $neighborhood;
        $this->description = $description;
        $this->estimatedCost = $estimatedCost;
        $this->urgent = $urgent;
        $this->category = $category;
        $this->clientPhone = $clientPhone;
        $this->clientAvatar = $clientAvatar;
        $this->workerSpecialty = $workerSpecialty;
        $this->workerAvatar = $workerAvatar;
        $this->diagnosisPhoto = $diagnosisPhoto;
        $this->hasReview = $hasReview;
        $this->state = $initialState ?? new PendingState();
        $this->createdAt = $createdAt ?? date('Y-m-d H:i');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i');
    }

    public static function stateFromStatus(string $status): RequestStateInterface {
        return match ($status) {
            'ACEPTADA' => new AcceptedState(),
            'RECHAZADA' => new RejectedState(),
            'PROGRAMADA' => new ScheduledState(),
            'EN_PROCESO' => new InProgressState(),
            'FINALIZADA' => new FinishedState(),
            'CANCELADA' => new CancelledState(),
            default => new PendingState(),
        };
    }

    public static function fromArray(array $data): self {
        $statusStr = $data['status'] ?? 'PENDIENTE';
        $state = self::stateFromStatus($statusStr);

        return new self(
            $data['id'],
            $data['code'],
            $data['client_id'],
            $data['client_name'],
            $data['worker_id'],
            $data['worker_name'],
            $data['service_title'],
            $data['scheduled_date'],
            $data['scheduled_time'],
            $data['address'],
            $data['neighborhood'],
            $data['description'],
            (int)$data['estimated_cost'],
            !empty($data['urgent']),
            $data['category'] ?? null,
            $data['client_phone'] ?? null,
            $data['client_avatar'] ?? null,
            $data['worker_specialty'] ?? null,
            $data['worker_avatar'] ?? null,
            $data['diagnosis_photo'] ?? null,
            !empty($data['has_review']),
            $state,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    // State Pattern Transitions
    public function transitionTo(RequestStateInterface $newState): void {
        $this->state = $newState;
        $this->updatedAt = date('Y-m-d H:i');
    }

    public function accept(string $actorRole, string $actorId): void {
        $this->state->accept($this, $actorRole, $actorId);
    }

    public function reject(string $actorRole, string $actorId, string $reason = ''): void {
        $this->state->reject($this, $actorRole, $actorId, $reason);
    }

    public function schedule(string $actorRole, string $actorId, string $date, string $time): void {
        $this->state->schedule($this, $actorRole, $actorId, $date, $time);
    }

    public function start(string $actorRole, string $actorId): void {
        $this->state->start($this, $actorRole, $actorId);
    }

    public function finish(string $actorRole, string $actorId): void {
        $this->state->finish($this, $actorRole, $actorId);
    }

    public function cancel(string $actorRole, string $actorId, string $reason = ''): void {
        $this->state->cancel($this, $actorRole, $actorId, $reason);
    }

    public function setSchedule(string $date, string $time): void {
        $this->scheduledDate = $date;
        $this->scheduledTime = $time;
        $this->updatedAt = date('Y-m-d H:i');
    }

    public function setHasReview(bool $hasReview): void {
        $this->hasReview = $hasReview;
        $this->updatedAt = date('Y-m-d H:i');
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getClientId(): string { return $this->clientId; }
    public function getClientName(): string { return $this->clientName; }
    public function getClientPhone(): ?string { return $this->clientPhone; }
    public function getClientAvatar(): ?string { return $this->clientAvatar; }
    public function getWorkerId(): string { return $this->workerId; }
    public function getWorkerName(): string { return $this->workerName; }
    public function getWorkerSpecialty(): ?string { return $this->workerSpecialty; }
    public function getWorkerAvatar(): ?string { return $this->workerAvatar; }
    public function getServiceTitle(): string { return $this->serviceTitle; }
    public function getCategory(): ?string { return $this->category; }
    public function getStatus(): string { return $this->state->getStatus(); }
    public function getState(): RequestStateInterface { return $this->state; }
    public function getScheduledDate(): string { return $this->scheduledDate; }
    public function getScheduledTime(): string { return $this->scheduledTime; }
    public function getAddress(): string { return $this->address; }
    public function getNeighborhood(): string { return $this->neighborhood; }
    public function getDescription(): string { return $this->description; }
    public function getEstimatedCost(): int { return $this->estimatedCost; }
    public function isUrgent(): bool { return $this->urgent; }
    public function getDiagnosisPhoto(): ?string { return $this->diagnosisPhoto; }
    public function hasReview(): bool { return $this->hasReview; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
            'client_phone' => $this->clientPhone,
            'client_avatar' => $this->clientAvatar,
            'worker_id' => $this->workerId,
            'worker_name' => $this->workerName,
            'worker_specialty' => $this->workerSpecialty,
            'worker_avatar' => $this->workerAvatar,
            'service_title' => $this->serviceTitle,
            'category' => $this->category,
            'status' => $this->getStatus(),
            'scheduled_date' => $this->scheduledDate,
            'scheduled_time' => $this->scheduledTime,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'description' => $this->description,
            'estimated_cost' => $this->estimatedCost,
            'urgent' => $this->urgent,
            'diagnosis_photo' => $this->diagnosisPhoto,
            'has_review' => $this->hasReview,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
