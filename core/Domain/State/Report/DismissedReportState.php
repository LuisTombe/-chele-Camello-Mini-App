<?php
namespace App\Domain\State\Report;

use App\Domain\Entity\Report;
use App\Domain\Exception\InvalidStateTransitionException;

class DismissedReportState implements ReportStateInterface {
    public function getStatus(): string {
        return 'DESCARTADO';
    }

    public function startReview(Report $report, string $adminUser): void {
        throw new InvalidStateTransitionException('DESCARTADO', 'EN_REVISION', 'El reporte ha sido descartado.');
    }

    public function resolve(Report $report, string $adminUser, string $resolutionNotes): void {
        throw new InvalidStateTransitionException('DESCARTADO', 'RESUELTO', 'Un reporte descartado no puede ser resuelto directamente.');
    }

    public function dismiss(Report $report, string $adminUser, string $reason): void {
        throw new InvalidStateTransitionException('DESCARTADO', 'DESCARTADO', 'El reporte ya está descartado.');
    }
}
