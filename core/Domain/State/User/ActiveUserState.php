<?php
namespace App\Domain\State\User;

use App\Domain\Entity\User;
use App\Domain\Exception\UnauthorizedActionException;

class ActiveUserState implements UserStateInterface {
    public function getStatus(): string {
        return 'ACTIVO';
    }

    public function canLogin(): bool {
        return true;
    }

    public function suspend(User $user, string $actorRole, string $reason): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede suspender cuentas de usuario.');
        }
        $user->transitionToState(new SuspendedUserState(), $reason);
    }

    public function block(User $user, string $actorRole, string $reason): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede bloquear cuentas de usuario.');
        }
        $user->transitionToState(new BlockedUserState(), $reason);
    }

    public function activate(User $user, string $actorRole): void {
        // Ya está activo
    }
}
