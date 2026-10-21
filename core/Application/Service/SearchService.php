<?php
namespace App\Application\Service;

use App\Domain\Strategy\Search\SearchSortContext;
use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\SearchResultDTO;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Repository\ServiceRepository;
use App\Infrastructure\Adapter\Geolocation\GeolocationAdapterInterface;

class SearchService {
    private WorkerRepository $workerRepo;
    private ServiceRepository $serviceRepo;
    private SearchSortContext $sortContext;
    private GeolocationAdapterInterface $geoAdapter;

    public function __construct(
        WorkerRepository $workerRepo,
        ServiceRepository $serviceRepo,
        SearchSortContext $sortContext,
        GeolocationAdapterInterface $geoAdapter
    ) {
        $this->workerRepo = $workerRepo;
        $this->serviceRepo = $serviceRepo;
        $this->sortContext = $sortContext;
        $this->geoAdapter = $geoAdapter;
    }

    /**
     * Búsqueda compuesta con filtrado, cálculo dinámico de distancia (Adapter) y ordenamiento (Strategy)
     */
    public function search(SearchWorkersDTO $dto, ?float $userLat = null, ?float $userLon = null): array {
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

        // 1. Consultar trabajadores del dominio propietario
        $workers = $this->workerRepo->findAll($filters);

        // 2. Si se suministran coordenadas o el usuario busca desde un barrio específico,
        // recalcular distancia dinámica usando el Adapter de Geolocalización (Haversine)
        $originLat = $userLat ?? 2.9273; // Centro de Neiva por defecto
        $originLon = $userLon ?? -75.2819;

        $results = [];
        foreach ($workers as $worker) {
            $data = $worker->toArray();

            if ($userLat !== null && $userLon !== null) {
                $calcDist = $this->geoAdapter->calculateDistanceToNeighborhood($worker->getNeighborhood(), $originLat, $originLon);
                $data['distance_km'] = $calcDist;
            }

            // Cargar servicios activos del catálogo
            $services = $this->serviceRepo->findByWorkerId($worker->getId(), true);
            $data['services'] = array_map(fn($s) => $s->toArray(), $services);

            $results[] = $data;
        }

        // 3. Aplicar ordenamiento dinámico mediante Patrón Strategy
        $this->sortContext->apply($results, $dto->sortBy);

        // 4. Retornar DTOs estructurados
        return array_map(fn($item) => (new SearchResultDTO($item))->toArray(), $results);
    }
}
