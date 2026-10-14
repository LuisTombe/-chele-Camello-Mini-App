<?php
namespace App\Domain\Strategy\Search;

class DistanceSortingStrategy implements SortingStrategyInterface {
    public function getCriterion(): string {
        return 'cercania';
    }

    public function supports(string $criterion): bool {
        return strtolower($criterion) === 'cercania' || strtolower($criterion) === 'distancia';
    }

    public function sort(array &$workers): void {
        usort($workers, function ($a, $b) {
            $distA = is_array($a) ? (float)($a['distance_km'] ?? 0) : $a->getDistanceKm();
            $distB = is_array($b) ? (float)($b['distance_km'] ?? 0) : $b->getDistanceKm();

            if ($distA != $distB) {
                return ($distA < $distB) ? -1 : 1;
            }

            // Desempate estable por ID
            $idA = is_array($a) ? $a['id'] : $a->getId();
            $idB = is_array($b) ? $b['id'] : $b->getId();
            return strcmp($idA, $idB);
        });
    }
}
