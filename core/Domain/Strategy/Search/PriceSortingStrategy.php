<?php
namespace App\Domain\Strategy\Search;

class PriceSortingStrategy implements SortingStrategyInterface {
    public function getCriterion(): string {
        return 'precio';
    }

    public function supports(string $criterion): bool {
        return strtolower($criterion) === 'precio' || strtolower($criterion) === 'tarifa';
    }

    public function sort(array &$workers): void {
        usort($workers, function ($a, $b) {
            $priceA = is_array($a) ? (int)($a['min_rate'] ?? 0) : $a->getMinRate();
            $priceB = is_array($b) ? (int)($b['min_rate'] ?? 0) : $b->getMinRate();

            if ($priceA != $priceB) {
                // Menor precio primero
                return ($priceA < $priceB) ? -1 : 1;
            }

            // Desempate por calificación
            $ratA = is_array($a) ? (float)($a['rating'] ?? 0) : $a->getRating();
            $ratB = is_array($b) ? (float)($b['rating'] ?? 0) : $b->getRating();
            if ($ratA != $ratB) {
                return ($ratA > $ratB) ? -1 : 1;
            }

            $idA = is_array($a) ? $a['id'] : $a->getId();
            $idB = is_array($b) ? $b['id'] : $b->getId();
            return strcmp($idA, $idB);
        });
    }
}
