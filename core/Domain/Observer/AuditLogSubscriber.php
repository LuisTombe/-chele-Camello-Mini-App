<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;
use App\Domain\Entity\AuditLog;
use App\Infrastructure\Repository\AuditLogRepository;

class AuditLogSubscriber implements EventSubscriberInterface {
    private AuditLogRepository $auditRepo;

    public function __construct(AuditLogRepository $auditRepo) {
        $this->auditRepo = $auditRepo;
    }

    public static function getSubscribedEvents(): array {
        return [
            'solicitud.creada' => 'onSolicitudCreada',
            'solicitud.aceptada' => 'onSolicitudTransicion',
            'solicitud.rechazada' => 'onSolicitudTransicion',
            'solicitud.programada' => 'onSolicitudTransicion',
            'solicitud.en_proceso' => 'onSolicitudTransicion',
            'solicitud.finalizada' => 'onSolicitudTransicion',
            'solicitud.cancelada' => 'onSolicitudTransicion',
            'reputacion.calificacion_registrada' => 'onCalificacionRegistrada',
            'moderacion.resuelta' => 'onModeracionResuelta',
        ];
    }

    public function onSolicitudCreada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $code = $p['code'] ?? 'CAM-SOL';
        $workerName = $p['worker_name'] ?? 'Técnico';
        $category = $p['category'] ?? 'General';

        $log = new AuditLog(
            'aud-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            'SERVICE_REQUEST_CREATED',
            $p['actor'] ?? 'usuario_cliente',
            date('Y-m-d H:i:s'),
            'schema_solicitudes',
            "Solicitud {$code} registrada para {$workerName} ({$category}).",
            'INFO'
        );
        $this->auditRepo->save($log);
    }

    public function onSolicitudTransicion(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $code = $p['code'] ?? $p['requestId'] ?? 'CAM-SOL';
        $status = $p['status'] ?? 'ACTUALIZADA';
        $actor = $p['actor'] ?? 'sistema';

        $log = new AuditLog(
            'aud-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            'STATUS_TRANSITION',
            $actor,
            date('Y-m-d H:i:s'),
            'schema_solicitudes',
            "Solicitud {$code} cambió a {$status}.",
            'INFO'
        );
        $this->auditRepo->save($log);
    }

    public function onCalificacionRegistrada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $workerId = $p['workerId'] ?? 'desconocido';
        $rating = $p['rating'] ?? 5;
        $avg = $p['avgRating'] ?? 5.0;

        $log = new AuditLog(
            'aud-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            'CALIFICACION_REGISTRADA',
            $p['actor'] ?? 'usuario_cliente',
            date('Y-m-d H:i:s'),
            'schema_reputacion',
            "Calificación de {$rating} estrellas registrada para trabajador ID: {$workerId}. Promedio: {$avg}.",
            'INFO'
        );
        $this->auditRepo->save($log);
    }

    public function onModeracionResuelta(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $reportId = $p['reportId'] ?? 'rep';
        $status = $p['status'] ?? 'RESUELTO';
        $admin = $p['adminUser'] ?? 'admin';

        $log = new AuditLog(
            'aud-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            'MODERACION_RESUELTA',
            $admin,
            date('Y-m-d H:i:s'),
            'schema_reputacion',
            "Reporte {$reportId} resuelto con estado {$status}.",
            'WARN'
        );
        $this->auditRepo->save($log);
    }
}
