<?php
namespace App\Domain\Entity;

class Worker {
    private string $id;
    private string $name;
    private ?string $email;
    private ?string $phone;
    private ?string $avatar;
    private string $specialty;
    private string $categoryId;
    private array $secondaryTrades;
    private ?string $bio;
    private int $experienceYears;
    private bool $senaCertified;
    private ?string $certificateTitle;
    private string $neighborhood;
    private float $distanceKm;
    private float $rating;
    private int $totalReviews;
    private int $completedJobs;
    private int $minRate;
    private bool $isAvailable;
    private ?string $workingHours;
    private array $portfolio;
    private string $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $specialty,
        string $categoryId,
        string $neighborhood,
        ?string $email = null,
        ?string $phone = null,
        ?string $avatar = null,
        array $secondaryTrades = [],
        ?string $bio = null,
        int $experienceYears = 0,
        bool $senaCertified = false,
        ?string $certificateTitle = null,
        float $distanceKm = 0.0,
        float $rating = 5.0,
        int $totalReviews = 0,
        int $completedJobs = 0,
        int $minRate = 0,
        bool $isAvailable = true,
        ?string $workingHours = null,
        array $portfolio = [],
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->specialty = $specialty;
        $this->categoryId = $categoryId;
        $this->neighborhood = $neighborhood;
        $this->email = $email;
        $this->phone = $phone;
        $this->avatar = $avatar;
        $this->secondaryTrades = $secondaryTrades;
        $this->bio = $bio;
        $this->experienceYears = $experienceYears;
        $this->senaCertified = $senaCertified;
        $this->certificateTitle = $certificateTitle;
        $this->distanceKm = $distanceKm;
        $this->rating = $rating;
        $this->totalReviews = $totalReviews;
        $this->completedJobs = $completedJobs;
        $this->minRate = $minRate;
        $this->isAvailable = $isAvailable;
        $this->workingHours = $workingHours;
        $this->portfolio = $portfolio;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['name'],
            $data['specialty'],
            $data['category_id'],
            $data['neighborhood'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['avatar'] ?? null,
            is_string($data['secondary_trades'] ?? null) ? (json_decode($data['secondary_trades'], true) ?: []) : ($data['secondary_trades'] ?? []),
            $data['bio'] ?? null,
            (int)($data['experience_years'] ?? 0),
            !empty($data['sena_certified']),
            $data['certificate_title'] ?? null,
            (float)($data['distance_km'] ?? 0.0),
            (float)($data['rating'] ?? 5.0),
            (int)($data['total_reviews'] ?? 0),
            (int)($data['completed_jobs'] ?? 0),
            (int)($data['min_rate'] ?? 0),
            !empty($data['is_available']),
            $data['working_hours'] ?? null,
            is_string($data['portfolio'] ?? null) ? (json_decode($data['portfolio'], true) ?: []) : ($data['portfolio'] ?? []),
            $data['created_at'] ?? null
        );
    }

    public function setAvailability(bool $available): void {
        $this->isAvailable = $available;
    }

    public function setSenaCertified(bool $certified, ?string $title = null): void {
        $this->senaCertified = $certified;
        if ($title !== null) {
            $this->certificateTitle = $title;
        }
    }

    public function updateRating(float $newRating, int $newTotalReviews): void {
        $this->rating = $newRating;
        $this->totalReviews = $newTotalReviews;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function getSpecialty(): string { return $this->specialty; }
    public function getCategoryId(): string { return $this->categoryId; }
    public function getSecondaryTrades(): array { return $this->secondaryTrades; }
    public function getBio(): ?string { return $this->bio; }
    public function getExperienceYears(): int { return $this->experienceYears; }
    public function isSenaCertified(): bool { return $this->senaCertified; }
    public function getCertificateTitle(): ?string { return $this->certificateTitle; }
    public function getNeighborhood(): string { return $this->neighborhood; }
    public function getDistanceKm(): float { return $this->distanceKm; }
    public function getRating(): float { return $this->rating; }
    public function getTotalReviews(): int { return $this->totalReviews; }
    public function getCompletedJobs(): int { return $this->completedJobs; }
    public function getMinRate(): int { return $this->minRate; }
    public function isAvailable(): bool { return $this->isAvailable; }
    public function getWorkingHours(): ?string { return $this->workingHours; }
    public function getPortfolio(): array { return $this->portfolio; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'specialty' => $this->specialty,
            'category_id' => $this->categoryId,
            'secondary_trades' => $this->secondaryTrades,
            'bio' => $this->bio,
            'experience_years' => $this->experienceYears,
            'sena_certified' => $this->senaCertified,
            'certificate_title' => $this->certificateTitle,
            'neighborhood' => $this->neighborhood,
            'distance_km' => $this->distanceKm,
            'rating' => $this->rating,
            'total_reviews' => $this->totalReviews,
            'completed_jobs' => $this->completedJobs,
            'min_rate' => $this->minRate,
            'is_available' => $this->isAvailable,
            'working_hours' => $this->workingHours,
            'portfolio' => $this->portfolio,
            'created_at' => $this->createdAt,
        ];
    }
}
