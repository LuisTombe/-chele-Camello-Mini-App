<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;
use App\Domain\Entity\ChatMessage;
use App\Infrastructure\Repository\ChatMessageRepository;

class ChatBotNotificationSubscriber implements EventSubscriberInterface {
    private ChatMessageRepository $chatRepo;

    public function __construct(ChatMessageRepository $chatRepo) {
        $this->chatRepo = $chatRepo;
    }

    public static function getSubscribedEvents(): array {
        return [
            'solicitud.creada' => 'onSolicitudCreada',
            'solicitud.aceptada' => 'onSolicitudAceptada',
            'solicitud.rechazada' => 'onSolicitudRechazada',
            'solicitud.programada' => 'onSolicitudProgramada',
            'solicitud.en_proceso' => 'onSolicitudEnProceso',
            'solicitud.finalizada' => 'onSolicitudFinalizada',
            'solicitud.cancelada' => 'onSolicitudCancelada',
        ];
    }

    public function onSolicitudCreada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $requestId = $p['id'] ?? '';
        $clientId = $p['client_id'] ?? 'cli-me';
        $clientName = $p['client_name'] ?? 'Cliente';
        $workerName = $p['worker_name'] ?? 'Técnico';
        $serviceTitle = $p['service_title'] ?? 'Servicio';
        $neighborhood = $p['neighborhood'] ?? 'Neiva';
        $desc = $p['description'] ?? '';

        $text = "¡Hola {$workerName}! He creado una solicitud para {$serviceTitle} en el barrio {$neighborhood}. Detalle: \"{$desc}\"";
        $msg = new ChatMessage(
            'msg-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            $requestId,
            $clientId,
            $clientName,
            'CLIENTE',
            $text,
            date('H:i')
        );
        $this->chatRepo->save($msg);
    }

    public function onSolicitudAceptada(DomainEventInterface $event): void {
        $this->sendBotNotice($event, 'El camellador ha aceptado el trabajo. Coordinando visita técnica.');
    }

    public function onSolicitudRechazada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $reason = !empty($p['reason']) ? " Motivo: {$p['reason']}" : ' Sin disponibilidad para esta franja.';
        $this->sendBotNotice($event, 'El camellador no tiene disponibilidad para esta solicitud.' . $reason);
    }

    public function onSolicitudProgramada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $date = $p['date'] ?? 'fecha acordada';
        $time = $p['time'] ?? '';
        $this->sendBotNotice($event, "El servicio ha sido agendado para {$date} a las {$time}.");
    }

    public function onSolicitudEnProceso(DomainEventInterface $event): void {
        $this->sendBotNotice($event, 'El técnico inició la labor en la dirección indicada.');
    }

    public function onSolicitudFinalizada(DomainEventInterface $event): void {
        $this->sendBotNotice($event, 'El camello ha sido marcado como finalizado con éxito.');
    }

    public function onSolicitudCancelada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $reason = !empty($p['reason']) ? " Motivo: {$p['reason']}" : '';
        $this->sendBotNotice($event, 'La solicitud ha sido cancelada.' . $reason);
    }

    private function sendBotNotice(DomainEventInterface $event, string $text): void {
        $p = $event->getPayload();
        $requestId = $p['requestId'] ?? $p['id'] ?? null;
        if (!$requestId) return;

        $msg = new ChatMessage(
            'msg-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2)),
            $requestId,
            'sistema',
            'Échele Camello Bot',
            'TRABAJADOR',
            '[ESTADO ACTUALIZADO]: ' . $text,
            date('H:i')
        );
        $this->chatRepo->save($msg);
    }
}
