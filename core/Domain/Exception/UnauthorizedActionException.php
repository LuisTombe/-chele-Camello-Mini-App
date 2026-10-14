<?php
namespace App\Domain\Exception;

class UnauthorizedActionException extends DomainException {
    public function __construct(string $message = 'Acción no autorizada para el rol o usuario actual.') {
        parent::__construct($message, 403);
    }
}
