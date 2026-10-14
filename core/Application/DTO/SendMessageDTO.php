<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class SendMessageDTO {
    public string $requestId;
    public string $text;
    public string $role;
    public string $senderName;
    public string $senderId;
    public ?string $attachmentUrl;

    public function __construct(array $data) {
        $errors = [];

        $this->requestId = trim($data['requestId'] ?? $data['request_id'] ?? '');
        if (empty($this->requestId)) {
            $errors[] = 'El identificador de la solicitud (requestId) es obligatorio.';
        }

        $this->text = trim($data['text'] ?? '');
        if ($this->text === '' && empty($data['attachmentUrl'])) {
            $errors[] = 'El texto del mensaje no puede estar vacío a menos que incluya un archivo adjunto.';
        }

        $this->role = strtoupper(trim($data['role'] ?? 'CLIENTE'));
        $this->senderName = trim($data['senderName'] ?? ($this->role === 'CLIENTE' ? 'Tú (Cliente)' : 'Técnico Especialista'));
        $this->senderId = trim($data['senderId'] ?? ($this->role === 'CLIENTE' ? 'cli-me' : 'w-1'));
        $this->attachmentUrl = !empty($data['attachmentUrl']) ? trim($data['attachmentUrl']) : null;

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
