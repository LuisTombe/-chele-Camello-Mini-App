<?php
namespace App\Application\DTO;

use App\Domain\Exception\ValidationException;

class CreateCategoryDTO {
    public string $id;
    public string $name;
    public string $iconName;
    public ?string $description;

    public function __construct(array $data) {
        $name = trim($data['name'] ?? '');
        $id = trim($data['id'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)));
        $iconName = trim($data['iconName'] ?? $data['icon_name'] ?? 'wrench');

        $errors = [];
        if ($name === '') {
            $errors[] = 'El nombre de la categoría es requerido.';
        }
        if ($id === '') {
            $errors[] = 'El identificador de la categoría es requerido.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->id = $id;
        $this->name = $name;
        $this->iconName = $iconName;
        $this->description = trim($data['description'] ?? '');
    }
}
