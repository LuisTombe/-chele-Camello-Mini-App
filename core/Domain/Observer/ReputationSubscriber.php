<?php
namespace App\Domain\Observer;

use App\Domain\Event\DomainEventInterface;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ReviewRepository;

class ReputationSubscriber implements EventSubscriberInterface {
    private WorkerRepository $workerRepo;
    private ReviewRepository $reviewRepo;

    public function __construct(WorkerRepository $workerRepo, ReviewRepository $reviewRepo) {
        $this->workerRepo = $workerRepo;
        $this->reviewRepo = $reviewRepo;
    }

    public static function getSubscribedEvents(): array {
        return [
            'reputacion.calificacion_registrada' => 'onCalificacionRegistrada',
        ];
    }

    public function onCalificacionRegistrada(DomainEventInterface $event): void {
        $p = $event->getPayload();
        $workerId = $p['workerId'] ?? null;
        if (!$workerId) return;

        // Recalcular agregado desde base de datos
        $stats = $this->reviewRepo->calculateWorkerAverage($workerId);
        $avgRating = $stats['avg'];
        $totalReviews = $stats['count'];

        $this->workerRepo->updateRating($workerId, $avgRating, $totalReviews);
    }
}
