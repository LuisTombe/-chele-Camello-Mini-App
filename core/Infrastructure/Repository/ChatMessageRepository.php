<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\ChatMessage;
use PDO;

class ChatMessageRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(ChatMessage $msg): void {
        $stmt = $this->pdo->prepare("INSERT INTO `chat_messages` (
            `id`, `request_id`, `sender_id`, `sender_name`, `sender_role`, `text`, `timestamp`, `attachment_url`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $msg->getId(),
            $msg->getRequestId(),
            $msg->getSenderId(),
            $msg->getSenderName(),
            $msg->getSenderRole(),
            $msg->getText(),
            $msg->getTimestamp(),
            $msg->getAttachmentUrl(),
            $msg->getCreatedAt()
        ]);
    }

    public function findByRequestId(string $requestId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM `chat_messages` WHERE `request_id` = ? ORDER BY `created_at` ASC");
        $stmt->execute([$requestId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($rows as $row) {
            $messages[] = ChatMessage::fromArray($row);
        }
        return $messages;
    }
}
