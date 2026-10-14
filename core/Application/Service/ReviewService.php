<?php
namespace App\Application\Service;

use App\Domain\Entity\Review;
use App\Domain\Event\CalificacionRegistradaEvent;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Observer\EventDispatcher;
use App\Application\DTO\CreateReviewDTO;
use App\Infrastructure\Repository\ReviewRepository;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ServiceRequestRepository;

class ReviewService {
    private ReviewRepository $reviewRepo;
    private WorkerRepository $workerRepo;
    private ServiceRequestRepository $requestRepo;
    private EventDispatcher $dispatcher;

    public function __construct(
        ReviewRepository $reviewRepo,
        WorkerRepository $workerRepo,
        ServiceRequestRepository $requestRepo,
        EventDispatcher $dispatcher
    ) {
        $this->reviewRepo = $reviewRepo;
        $this->workerRepo = $workerRepo;
        $this->requestRepo = $requestRepo;
        $this->dispatcher = $dispatcher;
    }

    public function createReview(CreateReviewDTO $dto): Review {
        // 1. Validar que el trabajador exista
        $worker = $this->workerRepo->findById($dto->workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $dto->workerId);
        }

        // 2. Si viene asociada a una solicitud, validar elegibilidad y estado FINALIZADA
        if ($dto->requestId) {
            $request = $this->requestRepo->findById($dto->requestId);
            if (!$request) {
                throw new EntityNotFoundException('Solicitud de servicio', $dto->requestId);
            }

            // REGLA: Solo se puede calificar un servicio efectivamente FINALIZADO
            if ($request->getStatus() !== 'FINALIZADA') {
                throw new ValidationException([
                    "Solo se puede calificar un servicio en estado FINALIZADA. El estado actual es: '{$request->getStatus()}'."
                ]);
            }

            // REGLA: Unicidad de calificación por solicitud
            if ($this->reviewRepo->hasReviewForRequest($dto->requestId)) {
                throw new ValidationException([
                    "Ya existe una calificación registrada para la solicitud {$request->getCode()}."
                ]);
            }
        }

        // 3. Crear y persistir la reseña
        $revId = 'rev-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $review = new Review(
            $revId,
            $dto->workerId,
            $dto->clientName,
            $dto->rating,
            $dto->requestId,
            $dto->clientAvatar,
            $dto->comment,
            'Hoy',
            $dto->serviceTitle
        );

        $this->reviewRepo->save($review);

        // 4. Marcar la solicitud como calificada
        if ($dto->requestId) {
            $reqToUpdate = $this->requestRepo->findById($dto->requestId);
            if ($reqToUpdate) {
                $reqToUpdate->setHasReview(true);
                $this->requestRepo->updateStatusAndSchedule($reqToUpdate);
            }
        }

        // 5. Emitir evento de dominio para que ReputationSubscriber y AuditLogSubscriber reaccionen
        $this->dispatcher->dispatch(new CalificacionRegistradaEvent([
            'reviewId' => $revId,
            'workerId' => $dto->workerId,
            'rating' => $dto->rating,
            'requestId' => $dto->requestId,
            'actor' => 'usuario_cliente'
        ]));

        return $review;
    }

    public function getWorkerReviews(string $workerId): array {
        return $this->reviewRepo->findByWorkerId($workerId);
    }
}
