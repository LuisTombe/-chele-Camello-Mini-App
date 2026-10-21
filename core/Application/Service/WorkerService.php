<?php
namespace App\Application\Service;

use App\Domain\Entity\Worker;
use App\Domain\Entity\Service;
use App\Domain\Entity\Category;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Strategy\Search\SearchSortContext;
use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Application\DTO\UpdateServiceDTO;
use App\Application\DTO\UpdateWorkerProfileDTO;
use App\Application\DTO\UpdateLocationDTO;
use App\Application\DTO\CreateCategoryDTO;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ServiceRepository;
use App\Infrastructure\Repository\ReviewRepository;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Adapter\Storage\FileStorageAdapterInterface;

class WorkerService {
    private WorkerRepository $workerRepo;
    private ServiceRepository $serviceRepo;
    private ReviewRepository $reviewRepo;
    private SearchSortContext $sortContext;
    private ?CategoryRepository $categoryRepo;
    private ?FileStorageAdapterInterface $storage;

    public function __construct(
        WorkerRepository $workerRepo,
        ServiceRepository $serviceRepo,
        ReviewRepository $reviewRepo,
        SearchSortContext $sortContext,
        ?CategoryRepository $categoryRepo = null,
        ?FileStorageAdapterInterface $storage = null
    ) {
        $this->workerRepo = $workerRepo;
        $this->serviceRepo = $serviceRepo;
        $this->reviewRepo = $reviewRepo;
        $this->sortContext = $sortContext;
        $this->categoryRepo = $categoryRepo;
        $this->storage = $storage;
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

    public function updateProfile(UpdateWorkerProfileDTO $dto): Worker {
        $worker = $this->workerRepo->findById($dto->workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $dto->workerId);
        }

        $this->workerRepo->updateProfile(
            $dto->workerId,
            $dto->name,
            $dto->phone,
            $dto->email,
            $dto->specialty,
            $dto->bio,
            $dto->experienceYears,
            $dto->minRate,
            $dto->workingHours,
            $dto->secondaryTrades
        );

        return $this->workerRepo->findById($dto->workerId);
    }

    public function updateLocation(UpdateLocationDTO $dto): void {
        $worker = $this->workerRepo->findById($dto->workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $dto->workerId);
        }

        $this->workerRepo->updateLocation($dto->workerId, $dto->neighborhood, $dto->distanceKm);
    }

    public function uploadAvatar(string $workerId, array $fileData): string {
        $worker = $this->workerRepo->findById($workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $workerId);
        }

        if (!$this->storage) {
            throw new ValidationException(['El servicio de almacenamiento no está disponible.']);
        }

        $avatarUrl = $this->storage->save($fileData, 'avatars');
        $this->workerRepo->updateAvatar($workerId, $avatarUrl);

        return $avatarUrl;
    }

    public function uploadCertificate(string $workerId, array $fileData, string $title): string {
        $worker = $this->workerRepo->findById($workerId);
        if (!$worker) {
            throw new EntityNotFoundException('Trabajador', $workerId);
        }

        if (!$this->storage) {
            throw new ValidationException(['El servicio de almacenamiento no está disponible.']);
        }

        $certUrl = $this->storage->save($fileData, 'certificates');
        $this->workerRepo->updateVerification($workerId, true, $title);

        return $certUrl;
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

    public function updateService(UpdateServiceDTO $dto): Service {
        $service = $this->serviceRepo->findById($dto->serviceId);
        if (!$service) {
            throw new EntityNotFoundException('Servicio', $dto->serviceId);
        }

        $service->updateDetails($dto->title, $dto->estimatedPrice, $dto->priceType, $dto->description);
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

    public function suspendService(string $serviceId, string $actorRole, string $reason): Service {
        $service = $this->serviceRepo->findById($serviceId);
        if (!$service) {
            throw new EntityNotFoundException('Servicio', $serviceId);
        }

        $service->suspend($actorRole, $reason);
        $this->serviceRepo->updateStatus($service->getId(), $service->getStatus(), false);
        return $service;
    }

    public function unsuspendService(string $serviceId, string $actorRole): Service {
        $service = $this->serviceRepo->findById($serviceId);
        if (!$service) {
            throw new EntityNotFoundException('Servicio', $serviceId);
        }

        $service->unsuspend($actorRole);
        $this->serviceRepo->updateStatus($service->getId(), $service->getStatus(), false);
        return $service;
    }

    // Métodos para ms-servicios (Categorías)
    public function getCategories(): array {
        if ($this->categoryRepo) {
            return array_map(fn($c) => $c->toArray(), $this->categoryRepo->findAll());
        }
        return [];
    }

    public function createCategory(CreateCategoryDTO $dto): Category {
        if (!$this->categoryRepo) {
            throw new ValidationException(['Repositorio de categorías no configurado.']);
        }

        $existing = $this->categoryRepo->findById($dto->id);
        if ($existing) {
            throw new ValidationException(["La categoría con identificador '{$dto->id}' ya existe."]);
        }

        $cat = new Category($dto->id, $dto->name, $dto->iconName, $dto->description);
        $this->categoryRepo->save($cat);
        return $cat;
    }
}
