<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class ScheduledState extends AbstractRequestState {
    public function getStatus(): string {
        return 'PROGRAMADA';
    }

    public function start(ServiceRequest $request, string $actorRole, string $actorId): void {
        $this->checkWorkerRole($actorRole);
        $request->transitionTo(new InProgressState());
    }

    public function schedule(ServiceRequest $request, string $actorRole, string $actorId, string $date, string $time): void {
        // Reprogramar fecha/hora
        $this->checkParticipantRole($actorRole);
        $request->setSchedule($date, $time);
    }

    public function cancel(ServiceRequest $request, string $actorRole, string $actorId, string $reason = ''): void {
        $this->checkParticipantRole($actorRole);
        $request->transitionTo(new CancelledState());
    }
}
