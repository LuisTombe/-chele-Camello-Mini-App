<?php
namespace App\Domain\Strategy\Search;

use App\Domain\Exception\ValidationException;

class SearchSortContext {
    /** @var SortingStrategyInterface[] */
    private array $strategies = [];

    public function __construct(array $strategies = []) {
        foreach ($strategies as $strategy) {
            $this->addStrategy($strategy);
        }
    }

    public function addStrategy(SortingStrategyInterface $strategy): void {
        $this->strategies[$strategy->getCriterion()] = $strategy;
    }

    public function apply(array &$workers, string $criterion = 'cercania'): string {
        $cleanCriterion = trim(strtolower($criterion));
        if ($cleanCriterion === '') {
            $cleanCriterion = 'cercania';
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($cleanCriterion)) {
                $strategy->sort($workers);
                return $strategy->getCriterion();
            }
        }

        $available = implode(', ', array_keys($this->strategies));
        throw new ValidationException(
            ["Criterio de ordenamiento '{$criterion}' no soportado. Criterios válidos: {$available}."]
        );
    }

    public function getAvailableCriteria(): array {
        return array_keys($this->strategies);
    }
}
