<?php
namespace App\Domain\State\User;

use App\Domain\Entity\User;

interface UserStateInterface {
    public function getStatus(): string;
    public function canLogin(): bool;
    public function suspend(User $user, string $actorRole, string $reason): void;
    public function block(User $user, string $actorRole, string $reason): void;
    public function activate(User $user, string $actorRole): void;
}
