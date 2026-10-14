<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class InProgressState extends AbstractRequestState {
    public function getStatus(): string {
        return 'EN_PROCESO';
    }

    public function finish(ServiceRequest $request, string $actorRole, string $actorId): void {
        $this->checkWorkerRole($actorRole);
        $request->transitionTo(new FinishedState());
    }

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        $this->checkParticipantRole($actorRole);
        $request->transitionTo(new CancelledState());
    }
}
