<?php
namespace App\Domain\Exception;

class EntityNotFoundException extends DomainException {
    public function __construct(string $entityName, string $id) {
        parent::__construct("No se encontró {$entityName} con identificador '{$id}'.", 404);
    }
}
