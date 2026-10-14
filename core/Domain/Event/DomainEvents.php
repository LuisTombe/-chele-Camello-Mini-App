<?php
namespace App\Domain\Event;

class SolicitudCreadaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.creada';
    }
}

class SolicitudAceptadaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.aceptada';
    }
}

class SolicitudRechazadaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.rechazada';
    }
}

class SolicitudProgramadaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.programada';
    }
}

class SolicitudEnProcesoEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.en_proceso';
    }
}

class SolicitudFinalizadaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.finalizada';
    }
}

class SolicitudCanceladaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'solicitud.cancelada';
    }
}

class CalificacionRegistradaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'reputacion.calificacion_registrada';
    }
}

class ModeracionResueltaEvent extends AbstractDomainEvent {
    public function getEventName(): string {
        return 'moderacion.resuelta';
    }
}
