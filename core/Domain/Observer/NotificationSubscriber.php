<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;
use App\Application\Service\NotificationService;

class NotificationSubscriber implements EventSubscriberInterface {
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    public static function getSubscribedEvents(): array {
        return [
            'solicitud.creada'                  => 'onSolicitudCreada',
            'solicitud.aceptada'                => 'onSolicitudAceptada',
            'solicitud.rechazada'               => 'onSolicitudRechazada',
            'solicitud.programada'              => 'onSolicitudProgramada',
            'solicitud.reprogramada'            => 'onSolicitudReprogramada',
            'solicitud.finalizada'              => 'onSolicitudFinalizada',
            'solicitud.cancelada'               => 'onSolicitudCancelada',
            'reputacion.calificacion_registrada' => 'onCalificacionRegistrada',
            'moderacion.resuelta'               => 'onModeracionResuelta',
        ];
    }

    public function onSolicitudCreada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $workerId = $payload['worker_id'] ?? $payload['workerId'] ?? null;
        $clientName = $payload['client_name'] ?? $payload['clientName'] ?? 'Un cliente';
        $serviceTitle = $payload['service_title'] ?? $payload['serviceTitle'] ?? 'Servicio';
        $code = $payload['code'] ?? 'CAM';

        if ($workerId) {
            $this->notificationService->createNotification(
                $workerId,
                "Nueva Solicitud: {$code}",
                "{$clientName} te ha solicitado el servicio '{$serviceTitle}'. Revisa y responde en tu taller.",
                'SOLICITUD',
                "#solicitud-{$code}"
            );
        }
    }

    public function onSolicitudAceptada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $clientId = $payload['client_id'] ?? $payload['clientId'] ?? 'cli-1';
        $code = $payload['code'] ?? 'CAM';

        $this->notificationService->createNotification(
            $clientId,
            "¡Solicitud Aceptada! ({$code})",
            "El técnico ha aceptado tu solicitud. Ya puedes coordinar detalles por el chat.",
            'SOLICITUD'
        );
    }

    public function onSolicitudRechazada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $clientId = $payload['client_id'] ?? $payload['clientId'] ?? 'cli-1';
        $code = $payload['code'] ?? 'CAM';
        $reason = $payload['reason'] ?? 'El trabajador no cuenta con disponibilidad inmediata.';

        $this->notificationService->createNotification(
            $clientId,
            "Solicitud No Aceptada ({$code})",
            "Tu solicitud no pudo ser atendida. Motivo: {$reason}",
            'SOLICITUD'
        );
    }

    public function onSolicitudProgramada(DomainEventInterface $event): void {
        $this->notificarCita($event->getPayload(), false);
    }

    public function onSolicitudReprogramada(DomainEventInterface $event): void {
        $this->notificarCita($event->getPayload(), true);
    }

    private function notificarCita(array $payload, bool $isReprogrammed): void {
        $clientId = $payload['client_id'] ?? $payload['clientId'] ?? 'cli-1';
        $workerId = $payload['worker_id'] ?? $payload['workerId'] ?? 'w-1';
        $code = $payload['code'] ?? 'CAM';
        $date = $payload['date'] ?? '';
        $time = $payload['time'] ?? '';

        $title = $isReprogrammed ? "Cita Reprogramada ({$code})" : "Cita Programada ({$code})";
        $msg = "La cita para el servicio {$code} quedó agendada para el {$date} a las {$time}.";

        $this->notificationService->createNotification($clientId, $title, $msg, 'SOLICITUD');
        $this->notificationService->createNotification($workerId, $title, $msg, 'SOLICITUD');
    }

    public function onSolicitudFinalizada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $clientId = $payload['client_id'] ?? $payload['clientId'] ?? 'cli-1';
        $code = $payload['code'] ?? 'CAM';

        $this->notificationService->createNotification(
            $clientId,
            "Servicio Completado ({$code})",
            "Tu camellador ha marcado el trabajo como finalizado. ¡Por favor califica su atención y mano de obra!",
            'SOLICITUD'
        );
    }

    public function onSolicitudCancelada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $clientId = $payload['client_id'] ?? $payload['clientId'] ?? 'cli-1';
        $workerId = $payload['worker_id'] ?? $payload['workerId'] ?? 'w-1';
        $code = $payload['code'] ?? 'CAM';
        $reason = $payload['reason'] ?? 'Cancelada por el usuario';
        $msg = "La solicitud {$code} ha sido cancelada. Motivo: {$reason}";

        $this->notificationService->createNotification($clientId, "Solicitud Cancelada", $msg, 'SOLICITUD');
        $this->notificationService->createNotification($workerId, "Solicitud Cancelada", $msg, 'SOLICITUD');
    }

    public function onCalificacionRegistrada(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $workerId = $payload['worker_id'] ?? $payload['workerId'] ?? null;
        $rating = $payload['rating'] ?? 5;

        if ($workerId) {
            $this->notificationService->createNotification(
                $workerId,
                "¡Nueva Calificación Recibida! ⭐ {$rating}",
                "Has recibido una nueva reseña en tu perfil profesional de Échele Camello.",
                'SISTEMA'
            );
        }
    }

    public function onModeracionResuelta(DomainEventInterface $event): void {
        $payload = $event->getPayload();
        $reporterId = $payload['reporter_id'] ?? null;
        $status = $payload['status'] ?? 'RESUELTO';

        if ($reporterId) {
            $this->notificationService->createNotification(
                $reporterId,
                "Reporte de Moderación Actualizado",
                "Tu reporte ha sido evaluado por el equipo de moderación con resolución: {$status}.",
                'MODERACION'
            );
        }
    }
}
