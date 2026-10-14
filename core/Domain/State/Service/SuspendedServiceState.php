<?php
namespace App\Domain\State\Service;

use App\Domain\Entity\Service;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;

class SuspendedServiceState implements ServiceStateInterface {
    public function getStatus(): string {
        return 'SUSPENDIDO';
    }

    public function activate(Service $service, string $actorRole): void {
        // El trabajador NO puede eludir una suspensión administrativa
        throw new UnauthorizedActionException('El servicio está suspendido por moderación administrativa. Solo un administrador puede reactivarlo.');
    }

    public function deactivate(Service $service, string $actorRole): void {
        throw new InvalidStateTransitionException('SUSPENDIDO', 'INACTIVO', 'El servicio está suspendido por moderación; debe ser resuelto por un administrador.');
    }

    public function suspend(Service $service, string $actorRole, string $reason = ''): void {
        // Ya está suspendido
    }

    public function unsuspend(Service $service, string $actorRole): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede levantar una suspensión.');
        }
        $service->transitionTo(new InactiveServiceState());
    }
}
