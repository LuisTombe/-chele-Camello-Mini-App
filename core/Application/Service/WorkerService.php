<?php
namespace App\Application\Service;

use App\Domain\Entity\Worker;
use App\Domain\Entity\Service;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Strategy\Search\SearchSortContext;
use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ServiceRepository;
use App\Infrastructure\Repository\ReviewRepository;

class WorkerService {
    private WorkerRepository $workerRepo;
    private ServiceRepository $serviceRepo;
    private ReviewRepository $reviewRepo;
    private SearchSortContext $sortContext;

    public function __construct(
        WorkerRepository $workerRepo,
        ServiceRepository $serviceRepo,
        ReviewRepository $reviewRepo,
        SearchSortContext $sortContext
    ) {
        $this->workerRepo = $workerRepo;
        $this->serviceRepo = $serviceRepo;
        $this->reviewRepo = $reviewRepo;
        $this->sortContext = $sortContext;
    }

    public function getWorkers(SearchWorkersDTO $dto): array {
        if ($dto->workerId) {
            return [$this->getWorkerDetail($dto->workerId)];
        }

        $filters = [];
        if ($dto->categoryId) {
            $filters['categoryId'] = $dto->categoryId;
        }
        if ($dto->neighborhood && strtolower($dto->neighborhood) !== 'todos') {
            $filters['neighborhood'] = $dto->neighborhood;
        }
        if ($dto->search !== '') {
            $filters['search'] = $dto->search;
        }

        // 1. Obtener trabajadores filtrados por consulta estructurada
        $workers = $this->workerRepo->findAll($filters);

        // 2. Aplicar ordenamiento mediante Strategy Pattern
        $this->sortContext->apply($workers, $dto->sortBy);

        // 3. Adjuntar servicios y resumen
        $result = [];
        foreach ($workers as $worker) {
            $wData = $worker->toArray();
            $services = $this->serviceRepo->findByWorkerId($worker->getId());
            $wData['services'] = array_map(fn($s) => $s->toArray(), $services);
            $result[] = $wData;
        }

        return $result;
    }

    public function getWorkerDetail(string $workerId): array {
        $worker = $this->workerRepo->findById($workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $workerId);
        }

        $wData = $worker->toArray();
        $services = $this->serviceRepo->findByWorkerId($workerId);
        $reviews = $this->reviewRepo->findByWorkerId($workerId);

        $wData['services'] = array_map(fn($s) => $s->toArray(), $services);
        $wData['reviews'] = array_map(fn($r) => $r->toArray(), $reviews);

        return $wData;
    }

    public function toggleAvailability(string $workerId, bool $available): void {
        $worker = $this->workerRepo->findById($workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $workerId);
        }
        $this->workerRepo->updateAvailability($workerId, $available);
    }

    public function toggleVerification(string $workerId, bool $certified, ?string $title = null): void {
        $worker = $this->workerRepo->findById($workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $workerId);
        }
        $this->workerRepo->updateVerification($workerId, $certified, $title);
    }

    public function addService(CreateServiceDTO $dto): Service {
        $worker = $this->workerRepo->findById($dto->workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $dto->workerId);
        }

        $newId = 'svc-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $service = new Service(
            $newId,
            $dto->workerId,
            $dto->title,
            $dto->estimatedPrice,
            $dto->priceType,
            $dto->categoryId,
            $dto->description
        );

        $this->serviceRepo->save($service);
        return $service;
    }

    public function toggleServiceStatus(string $serviceId, string $actorRole = 'TRABAJADOR'): Service {
        $service = $this->serviceRepo->findById($serviceId);
        if (!$service) {
            throw new EntityNotFoundException('Servicio', $serviceId);
        }

        // Usar patrón State para activar o desactivar respetando suspensiones administrativas
        if ($service->isActive()) {
            $service->deactivate($actorRole);
        } else {
            $service->activate($actorRole);
        }

        $this->serviceRepo->updateStatus($service->getId(), $service->getStatus(), $service->isActive());
        return $service;
    }
}
