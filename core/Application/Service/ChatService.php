<?php
namespace App\Application\Service;

use App\Domain\Entity\ChatMessage;
use App\Domain\Exception\EntityNotFoundException;
use App\Application\DTO\SendMessageDTO;
use App\Infrastructure\Repository\ChatMessageRepository;
use App\Infrastructure\Repository\ServiceRequestRepository;
use App\Infrastructure\Adapter\Storage\FileStorageAdapterInterface;

class ChatService {
    private ChatMessageRepository $chatRepo;
    private ServiceRequestRepository $requestRepo;
    private FileStorageAdapterInterface $storage;

    public function __construct(
        ChatMessageRepository $chatRepo,
        ServiceRequestRepository $requestRepo,
        FileStorageAdapterInterface $storage
    ) {
        $this->chatRepo = $chatRepo;
        $this->requestRepo = $requestRepo;
        $this->storage = $storage;
    }

    public function getMessages(string $requestId): array {
        return $this->chatRepo->findByRequestId($requestId);
    }

    public function sendMessage(SendMessageDTO $dto, ?array $uploadedFile = null): ChatMessage {
        // Validar que la solicitud exista
        $request = $this->requestRepo->findById($dto->requestId);
        if (!$request) {
            throw new EntityNotFoundException('Solicitud de chat', $dto->requestId);
        }

        $attachmentUrl = $dto->attachmentUrl;

        // Si se envió un archivo por multipart, procesarlo mediante el FileStorageAdapter
        if ($uploadedFile && !empty($uploadedFile['tmp_name'])) {
            $attachmentUrl = $this->storage->save($uploadedFile, 'chat');
        }

        $msgId = 'msg-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $msg = new ChatMessage(
            $msgId,
            $dto->requestId,
            $dto->senderId,
            $dto->senderName,
            $dto->role,
            $dto->text,
            date('H:i'),
            $attachmentUrl
        );

        $this->chatRepo->save($msg);
        return $msg;
    }
}
