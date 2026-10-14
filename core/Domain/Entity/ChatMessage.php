<?php
namespace App\Domain\Entity;

class ChatMessage {
    private string $id;
    private string $requestId;
    private string $senderId;
    private string $senderName;
    private string $senderRole;
    private string $text;
    private string $timestamp;
    private ?string $attachmentUrl;
    private string $createdAt;

    public function __construct(
        string $id,
        string $requestId,
        string $senderId,
        string $senderName,
        string $senderRole,
        string $text,
        string $timestamp,
        ?string $attachmentUrl = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->requestId = $requestId;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->senderRole = $senderRole;
        $this->text = $text;
        $this->timestamp = $timestamp;
        $this->attachmentUrl = $attachmentUrl;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['request_id'],
            $data['sender_id'],
            $data['sender_name'],
            $data['sender_role'],
            $data['text'],
            $data['timestamp'],
            $data['attachment_url'] ?? null,
            $data['created_at'] ?? null
        );
    }

    public function getId(): string { return $this->id; }
    public function getRequestId(): string { return $this->requestId; }
    public function getSenderId(): string { return $this->senderId; }
    public function getSenderName(): string { return $this->senderName; }
    public function getSenderRole(): string { return $this->senderRole; }
    public function getText(): string { return $this->text; }
    public function getTimestamp(): string { return $this->timestamp; }
    public function getAttachmentUrl(): ?string { return $this->attachmentUrl; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'request_id' => $this->requestId,
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'sender_role' => $this->senderRole,
            'text' => $this->text,
            'timestamp' => $this->timestamp,
            'attachment_url' => $this->attachmentUrl,
            'created_at' => $this->createdAt,
        ];
    }
}
