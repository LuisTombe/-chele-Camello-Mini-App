<?php
namespace App\Domain\Strategy\Search;

class RatingSortingStrategy implements SortingStrategyInterface {
    public function getCriterion(): string {
        return 'calificacion';
    }

    public function supports(string $criterion): bool {
        return strtolower($criterion) === 'calificacion' || strtolower($criterion) === 'rating';
    }

    public function sort(array &$workers): void {
        usort($workers, function ($a, $b) {
            $ratA = is_array($a) ? (float)($a['rating'] ?? 0) : $a->getRating();
            $ratB = is_array($b) ? (float)($b['rating'] ?? 0) : $b->getRating();

            if ($ratA != $ratB) {
                // Mayor calificación primero
                return ($ratA > $ratB) ? -1 : 1;
            }

            // Desempate por total de reseñas
            $revA = is_array($a) ? (int)($a['total_reviews'] ?? 0) : $a->getTotalReviews();
            $revB = is_array($b) ? (int)($b['total_reviews'] ?? 0) : $b->getTotalReviews();
            if ($revA != $revB) {
                return ($revA > $revB) ? -1 : 1;
            }

            // Desempate estable por ID
            $idA = is_array($a) ? $a['id'] : $a->getId();
            $idB = is_array($b) ? $b['id'] : $b->getId();
            return strcmp($idA, $idB);
        });
    }
}
