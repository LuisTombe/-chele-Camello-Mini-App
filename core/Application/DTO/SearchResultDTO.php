<?php
namespace App\Application\DTO;

class SearchResultDTO {
    public string $id;
    public string $name;
    public string $specialty;
    public string $neighborhood;
    public float $distanceKm;
    public float $rating;
    public int $totalReviews;
    public int $minRate;
    public bool $isAvailable;
    public bool $senaCertified;
    public ?string $certificateTitle;
    public ?string $avatar;
    public array $services;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->specialty = $data['specialty'];
        $this->neighborhood = $data['neighborhood'];
        $this->distanceKm = (float)($data['distance_km'] ?? $data['distanceKm'] ?? 0.0);
        $this->rating = (float)($data['rating'] ?? 5.0);
        $this->totalReviews = (int)($data['total_reviews'] ?? $data['totalReviews'] ?? 0);
        $this->minRate = (int)($data['min_rate'] ?? $data['minRate'] ?? 0);
        $this->isAvailable = !empty($data['is_available']);
        $this->senaCertified = !empty($data['sena_certified']);
        $this->certificateTitle = $data['certificate_title'] ?? null;
        $this->avatar = $data['avatar'] ?? null;
        $this->services = $data['services'] ?? [];
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'specialty' => $this->specialty,
            'neighborhood' => $this->neighborhood,
            'distance_km' => $this->distanceKm,
            'rating' => $this->rating,
            'total_reviews' => $this->totalReviews,
            'min_rate' => $this->minRate,
            'is_available' => $this->isAvailable,
            'sena_certified' => $this->senaCertified,
            'certificate_title' => $this->certificateTitle,
            'avatar' => $this->avatar,
            'services' => $this->services,
        ];
    }
}
