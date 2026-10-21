<?php
namespace App\Domain\Entity;

class Category {
    private string $id;
    private string $name;
    private string $iconName;
    private ?string $description;
    private int $technicianCount;

    public function __construct(
        string $id,
        string $name,
        string $iconName = 'wrench',
        ?string $description = null,
        int $technicianCount = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->iconName = $iconName;
        $this->description = $description;
        $this->technicianCount = max(0, $technicianCount);
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['name'],
            $data['icon_name'] ?? 'wrench',
            $data['description'] ?? null,
            (int)($data['technician_count'] ?? 0)
        );
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getIconName(): string { return $this->iconName; }
    public function getDescription(): ?string { return $this->description; }
    public function getTechnicianCount(): int { return $this->technicianCount; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon_name' => $this->iconName,
            'description' => $this->description,
            'technician_count' => $this->technicianCount,
        ];
    }
}
