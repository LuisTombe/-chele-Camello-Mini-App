<?php
namespace App\Domain\Strategy\Search;

class ExperienceSortingStrategy implements SortingStrategyInterface {
    public function getCriterion(): string {
        return 'experiencia';
    }

    public function supports(string $criterion): bool {
        return strtolower($criterion) === 'experiencia' || strtolower($criterion) === 'anios';
    }

    public function sort(array &$workers): void {
        usort($workers, function ($a, $b) {
            $expA = is_array($a) ? (int)($a['experience_years'] ?? 0) : $a->getExperienceYears();
            $expB = is_array($b) ? (int)($b['experience_years'] ?? 0) : $b->getExperienceYears();

            if ($expA != $expB) {
                // Mayor experiencia primero
                return ($expA > $expB) ? -1 : 1;
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
