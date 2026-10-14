<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class AcceptedState extends AbstractRequestState {
    public function getStatus(): string {
        return 'ACEPTADA';
    }

    public function schedule(ServiceRequest $request, string $actorRole, string $actorId, string $date, string $time): void {
        $this->checkParticipantRole($actorRole);
        $request->setSchedule($date, $time);
        $request->transitionTo(new ScheduledState());
    }

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        $this->checkParticipantRole($actorRole);
        $request->transitionTo(new CancelledState());
    }
}
