<?php
namespace App\Domain\State\Service;

use App\Domain\Entity\Service;

interface ServiceStateInterface {
    public function getStatus(): string;

    public function activate(Service $service, string $actorRole): void;

    public function deactivate(Service $service, string $actorRole): void;

    public function suspend(Service $service, string $actorRole, string $reason = ''): void;

    public function unsuspend(Service $service, string $actorRole): void;
}
