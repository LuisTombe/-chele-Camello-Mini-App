<?php
namespace App\Application\DTO;

use App\Domain\Entity\Notification;

class NotificationDTO {
    public string $id;
    public string $userId;
    public string $title;
    public string $message;
    public string $type;
    public ?string $link;
    public bool $isRead;
    public string $createdAt;

    public function __construct(Notification $n) {
        $this->id = $n->getId();
        $this->userId = $n->getUserId();
        $this->title = $n->getTitle();
        $this->message = $n->getMessage();
        $this->type = $n->getType();
        $this->link = $n->getLink();
        $this->isRead = $n->isRead();
        $this->createdAt = $n->getCreatedAt();
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'isRead' => $this->isRead,
            'createdAt' => $this->createdAt,
        ];
    }
}
