<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class PendingState extends AbstractRequestState {
    public function getStatus(): string {
        return 'PENDIENTE';
    }

    public function accept(ServiceRequest $request, string $actorRole, string $actorId): void {
        $this->checkWorkerRole($actorRole);
        $request->transitionTo(new AcceptedState());
    }

    public function reject(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        $this->checkWorkerRole($actorRole);
        $request->transitionTo(new RejectedState());
    }

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        $this->checkParticipantRole($actorRole);
        $request->transitionTo(new CancelledState());
    }
}
