<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;
use App\Application\Service\WorkerService;
use App\Application\Service\AuthService;

class ModerationActionSubscriber implements EventSubscriberInterface {
    private WorkerService $workerService;
    private AuthService $authService;

    public function __construct(WorkerService $workerService, AuthService $authService) {
        $this->workerService = $workerService;
        $this->authService = $authService;
    }

    public static function getSubscribedEvents(): array {
        return [
            'moderacion.resuelta' => 'onModeracionResuelta',
        ];
    }

    public function onModeracionResuelta(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $status = $payload['status'] ?? '';
        $targetType = strtoupper($payload['target_type'] ?? $payload['targetType'] ?? '');
        $targetId = $payload['target_id'] ?? $payload['targetId'] ?? '';
        $notes = $payload['resolution_notes'] ?? $payload['resolutionNotes'] ?? '';

        // Si la moderación resuelve sancionar o suspender:
        $shouldSuspend = (
            $status === 'RESUELTO' && 
            (stripos($notes, 'suspender') !== false || stripos($notes, 'sancion') !== false || stripos($notes, 'bloqueo') !== false)
        );

        if ($shouldSuspend && $targetId !== '') {
            if ($targetType === 'SERVICE') {
                $this->workerService->suspendService($targetId, 'ADMINISTRADOR', "Moderación: {$notes}");
            } elseif ($targetType === 'WORKER' || $targetType === 'USER') {
                $this->authService->suspendUser($targetId, 'ADMINISTRADOR', "Moderación: {$notes}");
            }
        }
    }
}
