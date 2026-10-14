<?php
namespace App\Domain\Exception;

class InvalidStateTransitionException extends DomainException {
    public function __construct(string $fromStatus, string $toStatus, string $reason = '') {
        $msg = "Transición inválida de estado: '{$fromStatus}' -> '{$toStatus}'.";
        if ($reason) {
            $msg .= " Motivo: {$reason}";
        }
        parent::__construct($msg, 400);
    }
}
