<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;

abstract class AbstractRequestState implements RequestStateInterface {
    abstract public function getStatus(): string;

    public function accept(ServiceRequest $request, string $actorRole, string $actorId): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'ACEPTADA', 'No se puede aceptar desde el estado actual.');
    }

    public function reject(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'RECHAZADA', 'No se puede rechazar desde el estado actual.');
    }

    public function schedule(ServiceRequest $request, string $actorRole, string $actorId, string $date, string $time): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'PROGRAMADA', 'No se puede agendar/programar desde el estado actual.');
    }

    public function start(ServiceRequest $request, string $actorRole, string $actorId): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'EN_PROCESO', 'No se puede iniciar el trabajo desde el estado actual.');
    }

    public function finish(ServiceRequest $request, string $actorRole, string $actorId): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'FINALIZADA', 'No se puede finalizar el servicio desde el estado actual.');
    }

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        throw new InvalidStateTransitionException($this->getStatus(), 'CANCELADA', 'No se puede cancelar desde el estado actual.');
    }

    protected function checkWorkerRole(string $actorRole): void {
        if ($actorRole !== 'TRABAJADOR' && $actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo el trabajador asignado o un administrador puede realizar esta acción.');
        }
    }

    protected function checkParticipantRole(string $actorRole): void {
        if (!in_array($actorRole, ['CLIENTE', 'TRABAJADOR', 'ADMINISTRADOR'], true)) {
            throw new UnauthorizedActionException('Rol no autorizado para participar en la solicitud.');
        }
    }
}
