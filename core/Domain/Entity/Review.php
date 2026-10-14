<?php
namespace App\Domain\Entity;

class Review {
    private string $id;
    private string $workerId;
    private ?string $requestId;
    private string $clientName;
    private ?string $clientAvatar;
    private int $rating;
    private ?string $comment;
    private string $reviewDate;
    private ?string $serviceTitle;
    private string $createdAt;

    public function __construct(
        string $id,
        string $workerId,
        string $clientName,
        int $rating,
        ?string $requestId = null,
        ?string $clientAvatar = null,
        ?string $comment = null,
        string $reviewDate = 'Hoy',
        ?string $serviceTitle = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->workerId = $workerId;
        $this->clientName = $clientName;
        $this->rating = $rating;
        $this->requestId = $requestId;
        $this->clientAvatar = $clientAvatar;
        $this->comment = $comment;
        $this->reviewDate = $reviewDate;
        $this->serviceTitle = $serviceTitle;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['worker_id'],
            $data['client_name'],
            (int)$data['rating'],
            $data['request_id'] ?? null,
            $data['client_avatar'] ?? null,
            $data['comment'] ?? null,
            $data['review_date'] ?? 'Hoy',
            $data['service_title'] ?? null,
            $data['created_at'] ?? null
        );
    }

    public function getId(): string { return $this->id; }
    public function getWorkerId(): string { return $this->workerId; }
    public function getRequestId(): ?string { return $this->requestId; }
    public function getClientName(): string { return $this->clientName; }
    public function getClientAvatar(): ?string { return $this->clientAvatar; }
    public function getRating(): int { return $this->rating; }
    public function getComment(): ?string { return $this->comment; }
    public function getReviewDate(): string { return $this->reviewDate; }
    public function getServiceTitle(): ?string { return $this->serviceTitle; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'worker_id' => $this->workerId,
            'request_id' => $this->requestId,
            'client_name' => $this->clientName,
            'client_avatar' => $this->clientAvatar,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'review_date' => $this->reviewDate,
            'service_title' => $this->serviceTitle,
            'created_at' => $this->createdAt,
        ];
    }
}
