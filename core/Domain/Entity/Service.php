<?php
namespace App\Domain\Entity;

use App\Domain\State\Service\ServiceStateInterface;
use App\Domain\State\Service\ActiveServiceState;
use App\Domain\State\Service\InactiveServiceState;
use App\Domain\State\Service\SuspendedServiceState;

class Service {
    private string $id;
    private string $workerId;
    private ?string $categoryId;
    private string $title;
    private ?string $description;
    private int $estimatedPrice;
    private string $priceType;
    private ServiceStateInterface $state;
    private string $createdAt;

    public function __construct(
        string $id,
        string $workerId,
        string $title,
        int $estimatedPrice,
        string $priceType = 'desde',
        ?string $categoryId = null,
        ?string $description = null,
        ?ServiceStateInterface $state = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->workerId = $workerId;
        $this->title = $title;
        $this->estimatedPrice = $estimatedPrice;
        $this->priceType = $priceType;
        $this->categoryId = $categoryId;
        $this->description = $description;
        $this->state = $state ?? new ActiveServiceState();
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function stateFromStatus(string $status, bool $isActive = true): ServiceStateInterface {
        if ($status === 'SUSPENDIDO') {
            return new SuspendedServiceState();
        }
        if ($status === 'INACTIVO' || !$isActive) {
            return new InactiveServiceState();
        }
        return new ActiveServiceState();
    }

    public static function fromArray(array $data): self {
        $statusStr = $data['status'] ?? ($data['is_active'] ? 'ACTIVO' : 'INACTIVO');
        $isActive = !empty($data['is_active']);
        $state = self::stateFromStatus($statusStr, $isActive);

        return new self(
            $data['id'],
            $data['worker_id'],
            $data['title'],
            (int)$data['estimated_price'],
            $data['price_type'] ?? 'desde',
            $data['category_id'] ?? null,
            $data['description'] ?? null,
            $state,
            $data['created_at'] ?? null
        );
    }

    // State Pattern Transitions
    public function transitionTo(ServiceStateInterface $newState): void {
        $this->state = $newState;
    }

    public function activate(string $actorRole): void {
        $this->state->activate($this, $actorRole);
    }

    public function deactivate(string $actorRole): void {
        $this->state->deactivate($this, $actorRole);
    }

    public function suspend(string $actorRole, string $reason = ''): void {
        $this->state->suspend($this, $actorRole, $reason);
    }

    public function unsuspend(string $actorRole): void {
        $this->state->unsuspend($this, $actorRole);
    }

    public function getId(): string { return $this->id; }
    public function getWorkerId(): string { return $this->workerId; }
    public function getCategoryId(): ?string { return $this->categoryId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getEstimatedPrice(): int { return $this->estimatedPrice; }
    public function getPriceType(): string { return $this->priceType; }
    public function getStatus(): string { return $this->state->getStatus(); }
    public function isActive(): bool { return $this->state->getStatus() === 'ACTIVO'; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'worker_id' => $this->workerId,
            'category_id' => $this->categoryId,
            'title' => $this->title,
            'description' => $this->description,
            'estimated_price' => $this->estimatedPrice,
            'price_type' => $this->priceType,
            'is_active' => $this->isActive(),
            'status' => $this->getStatus(),
            'created_at' => $this->createdAt,
        ];
    }
}
