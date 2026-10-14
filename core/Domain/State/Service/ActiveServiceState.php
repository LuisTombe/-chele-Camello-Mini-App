<?php
namespace App\Domain\State\Service;

use App\Domain\Entity\Service;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;

class ActiveServiceState implements ServiceStateInterface {
    public function getStatus(): string {
        return 'ACTIVO';
    }

    public function activate(Service $service, string $actorRole): void {
        // Ya está activo
    }

    public function deactivate(Service $service, string $actorRole): void {
        // Trabajador o admin pueden desactivar
        if ($actorRole !== 'TRABAJADOR' && $actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo el trabajador o administrador pueden desactivar el servicio.');
        }
        $service->transitionTo(new InactiveServiceState());
    }

    public function suspend(Service $service, string $actorRole, string $reason = ''): void {
        // Solo moderador / admin puede suspender
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede suspender un servicio por moderación.');
        }
        $service->transitionTo(new SuspendedServiceState());
    }

    public function unsuspend(Service $service, string $actorRole): void {
        throw new InvalidStateTransitionException('ACTIVO', 'ACTIVO', 'El servicio no se encuentra suspendido.');
    }
}
