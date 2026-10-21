<?php
namespace App\Domain\State\User;

use App\Domain\Entity\User;
use App\Domain\Exception\UnauthorizedActionException;

class SuspendedUserState implements UserStateInterface {
    public function getStatus(): string {
        return 'SUSPENDIDO';
    }

    public function canLogin(): bool {
        return false;
    }

    public function suspend(User $user, string $actorRole, string $reason): void {
        // Ya está suspendido
    }

    public function block(User $user, string $actorRole, string $reason): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede bloquear cuentas de usuario.');
        }
        $user->transitionToState(new BlockedUserState(), $reason);
    }

    public function activate(User $user, string $actorRole): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede reactivar una cuenta suspendida.');
        }
        $user->transitionToState(new ActiveUserState(), null);
    }
}
