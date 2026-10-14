<?php
namespace App\Domain\Strategy\Search;

interface SortingStrategyInterface {
    public function getCriterion(): string;

    public function supports(string $criterion): bool;

    /**
     * Ordena el array de trabajadores de forma determinista y estable
     */
    public function sort(array &$workers): void;
}
