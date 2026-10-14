<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class CancelledState extends AbstractRequestState {
    public function getStatus(): string {
        return 'CANCELADA';
    }
    // Estado terminal: no permite transiciones
}
