<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

interface RequestStateInterface {
    public function getStatus(): string;

    public function accept(ServiceRequest $request, string $actorRole, string $actorId): void;

    public function reject(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void;

    public function schedule(ServiceRequest $request, string $actorRole, string $actorId, string $date, string $time): void;

    public function start(ServiceRequest $request, string $actorRole, string $actorId): void;

    public function finish(ServiceRequest $request, string $actorRole, string $actorId): void;

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void;
}
