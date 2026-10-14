<?php
namespace App\Domain\State\Request;

use App\Domain\Entity\ServiceRequest;

class FinishedState extends AbstractRequestState {
    public function getStatus(): string {
        return 'FINALIZADA';
    }
    // Estado terminal: no permite transiciones
}
