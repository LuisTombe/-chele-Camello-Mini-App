<?php
namespace App\Domain\Exception;

class ValidationException extends DomainException {
    private array $errors;

    public function __construct(array $errors, string $message = 'Error de validación en los datos proporcionados.') {
        $this->errors = $errors;
        $details = implode('; ', $errors);
        parent::__construct("{$message} Detalle: {$details}", 422);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}
