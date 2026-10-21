<?php
namespace App\Domain\Entity;

class Notification {
    private string $id;
    private string $userId;
    private string $title;
    private string $message;
    private string $type;
    private ?string $link;
    private bool $isRead;
    private string $createdAt;

    public function __construct(
        string $id,
        string $userId,
        string $title,
        string $message,
        string $type = 'SOLICITUD',
        ?string $link = null,
        bool $isRead = false,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = strtoupper($type);
        $this->link = $link;
        $this->isRead = $isRead;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['user_id'],
            $data['title'],
            $data['message'],
            $data['type'] ?? 'SOLICITUD',
            $data['link'] ?? null,
            !empty($data['is_read']),
            $data['created_at'] ?? null
        );
    }

    public function markAsRead(): void {
        $this->isRead = true;
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getType(): string { return $this->type; }
    public function getLink(): ?string { return $this->link; }
    public function isRead(): bool { return $this->isRead; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt,
        ];
    }
}
