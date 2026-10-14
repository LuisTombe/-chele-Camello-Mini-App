<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class RejectedState extends AbstractRequestState {
    public function getStatus(): string {
        return 'RECHAZADA';
    }
    // Estado terminal: no permite transiciones (no se puede continuar a programación)
}
