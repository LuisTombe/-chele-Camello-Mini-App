<?php
namespace App\Domain\State\Service;

use App\Domain\Entity\Service;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;

class InactiveServiceState implements ServiceStateInterface {
    public function getStatus(): string {
        return 'INACTIVO';
    }

    public function activate(Service $service, string $actorRole): void {
        if ($actorRole !== 'TRABAJADOR' && $actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo el trabajador o administrador pueden activar el servicio.');
        }
        $service->transitionTo(new ActiveServiceState());
    }

    public function deactivate(Service $service, string $actorRole): void {
        // Ya está inactivo
    }

    public function suspend(Service $service, string $actorRole, string $reason = ''): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede suspender un servicio.');
        }
        $service->transitionTo(new SuspendedServiceState());
    }

    public function unsuspend(Service $service, string $actorRole): void {
        throw new InvalidStateTransitionException('INACTIVO', 'INACTIVO', 'El servicio no se encuentra suspendido.');
    }
}
