<?php
namespace App\Application\DTO;

class SearchWorkersDTO {
    public ?string $workerId;
    public ?string $categoryId;
    public ?string $neighborhood;
    public string $search;
    public string $sortBy;

    public function __construct(array $data) {
        $this->workerId = !empty($data['id']) ? trim($data['id']) : null;
        $this->categoryId = !empty($data['category']) ? trim($data['category']) : null;
        $this->neighborhood = !empty($data['neighborhood']) ? trim($data['neighborhood']) : null;
        $this->search = trim($data['search'] ?? '');
        $this->sortBy = trim(strtolower($data['sortBy'] ?? $data['sort'] ?? 'cercania'));
    }
}
