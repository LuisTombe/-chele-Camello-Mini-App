<?php
namespace App\Application\Service;

use App\Domain\Entity\Notification;
use App\Application\DTO\NotificationDTO;
use App\Application\DTO\MarkNotificationReadDTO;
use App\Infrastructure\Repository\NotificationRepository;
use App\Infrastructure\Adapter\Mail\MailAdapterInterface;

class NotificationService {
    private NotificationRepository $notificationRepo;
    private ?MailAdapterInterface $mailer;

    public function __construct(NotificationRepository $notificationRepo, ?MailAdapterInterface $mailer = null) {
        $this->notificationRepo = $notificationRepo;
        $this->mailer = $mailer;
    }

    public function getUserNotifications(string $userId, bool $unreadOnly = false): array {
        $notifications = $this->notificationRepo->findByUserId($userId, $unreadOnly);
        $unreadCount = $this->notificationRepo->getUnreadCount($userId);

        return [
            'notifications' => array_map(fn($n) => (new NotificationDTO($n))->toArray(), $notifications),
            'unreadCount' => $unreadCount,
        ];
    }

    public function markAsRead(MarkNotificationReadDTO $dto): void {
        if ($dto->markAll) {
            $this->notificationRepo->markAllAsRead($dto->userId);
        } else if ($dto->notificationId) {
            $this->notificationRepo->markAsRead($dto->notificationId, $dto->userId);
        }
    }

    public function createNotification(
        string $userId,
        string $title,
        string $message,
        string $type = 'SOLICITUD',
        ?string $link = null,
        bool $sendEmail = false,
        ?string $userEmail = null
    ): Notification {
        $id = 'notif-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $notification = new Notification($id, $userId, $title, $message, $type, $link);

        $this->notificationRepo->save($notification);

        if ($sendEmail && $userEmail && $this->mailer) {
            $body = "<h3>{$title}</h3><p>{$message}</p>" . ($link ? "<p><a href='{$link}'>Ver detalles</a></p>" : "");
            $this->mailer->send($userEmail, $title . ' — Échele Camello', $body, true);
        }

        return $notification;
    }
}
