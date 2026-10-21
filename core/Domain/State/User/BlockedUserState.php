<?php
namespace App\Domain\State\User;

use App\Domain\Entity\User;
use App\Domain\Exception\UnauthorizedActionException;

class BlockedUserState implements UserStateInterface {
    public function getStatus(): string {
        return 'BLOQUEADO';
    }

    public function canLogin(): bool {
        return false;
    }

    public function suspend(User $user, string $actorRole, string $reason): void {
        // Un usuario bloqueado no pasa a suspensión
    }

    public function block(User $user, string $actorRole, string $reason): void {
        // Ya está bloqueado
    }

    public function activate(User $user, string $actorRole): void {
        if ($actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador con permisos supremos puede desbloquear esta cuenta.');
        }
        $user->transitionToState(new ActiveUserState(), null);
    }
}
